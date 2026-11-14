<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Throwable;

final class Booking extends BaseModel
{
    public const DURATION_MINUTES = 120;

    public function availableTables(string $date, string $time, int $guestCount): array
    {
        $start = $this->bookingDateTime($date, $time);
        $end = $start->modify('+' . self::DURATION_MINUTES . ' minutes');
        $isFutureDate = $date > (new DateTimeImmutable('today'))->format('Y-m-d');

        $statement = $this->db->prepare(
            'SELECT t.id, t.code, t.name, t.capacity, t.status, a.name AS area_name
             FROM restaurant_tables t
             INNER JOIN dining_areas a ON a.id = t.area_id
             WHERE t.capacity >= :guest_count
               AND t.status <> :locked_status
               AND (:is_future_date = 1 OR t.status IN (:empty_status, :reserved_status))
               AND NOT EXISTS (
                   SELECT 1
                   FROM bookings b
                   WHERE b.table_id = t.id
                     AND b.status IN (:pending_status, :confirmed_status, :seated_status)
                     AND TIMESTAMP(b.booking_date, b.booking_time) < :candidate_end
                     AND DATE_ADD(
                         TIMESTAMP(b.booking_date, b.booking_time),
                         INTERVAL ' . self::DURATION_MINUTES . ' MINUTE
                     ) > :candidate_start
               )
             ORDER BY a.sort_order ASC, t.capacity ASC, t.code ASC'
        );
        $statement->execute([
            'guest_count' => $guestCount,
            'locked_status' => 'locked',
            'is_future_date' => $isFutureDate ? 1 : 0,
            'empty_status' => 'empty',
            'reserved_status' => 'reserved',
            'pending_status' => 'pending',
            'confirmed_status' => 'confirmed',
            'seated_status' => 'seated',
            'candidate_start' => $start->format('Y-m-d H:i:s'),
            'candidate_end' => $end->format('Y-m-d H:i:s'),
        ]);

        return $statement->fetchAll();
    }

    public function createReservation(array $booking, array $cart, array $dishNotes): int
    {
        $this->db->beginTransaction();

        try {
            $tableStatement = $this->db->prepare(
                'SELECT id, area_id, capacity, status
                 FROM restaurant_tables
                 WHERE id = :id
                 FOR UPDATE'
            );
            $tableStatement->execute(['id' => $booking['table_id']]);
            $table = $tableStatement->fetch();
            if (
                $table === false
                || (int) $table['capacity'] < $booking['number_of_guests']
                || $table['status'] === 'locked'
                || ($booking['booking_date'] === date('Y-m-d')
                    && !in_array($table['status'], ['empty', 'reserved'], true))
            ) {
                throw new DomainException('table_unavailable');
            }

            $start = $this->bookingDateTime($booking['booking_date'], $booking['booking_time']);
            $end = $start->modify('+' . self::DURATION_MINUTES . ' minutes');
            $overlapStatement = $this->db->prepare(
                'SELECT id
                 FROM bookings
                 WHERE table_id = :table_id
                   AND status IN (:pending_status, :confirmed_status, :seated_status)
                   AND TIMESTAMP(booking_date, booking_time) < :candidate_end
                   AND DATE_ADD(
                       TIMESTAMP(booking_date, booking_time),
                       INTERVAL ' . self::DURATION_MINUTES . ' MINUTE
                   ) > :candidate_start
                 LIMIT 1
                 FOR UPDATE'
            );
            $overlapStatement->execute([
                'table_id' => $booking['table_id'],
                'pending_status' => 'pending',
                'confirmed_status' => 'confirmed',
                'seated_status' => 'seated',
                'candidate_start' => $start->format('Y-m-d H:i:s'),
                'candidate_end' => $end->format('Y-m-d H:i:s'),
            ]);
            if ($overlapStatement->fetchColumn() !== false) {
                throw new DomainException('table_unavailable');
            }

            $orderableDishes = $this->lockOrderableDishes(array_keys($cart));
            if (count($orderableDishes) !== count($cart)) {
                throw new DomainException('dish_unavailable');
            }

            $insertBooking = $this->db->prepare(
                'INSERT INTO bookings
                    (user_id, customer_name, customer_phone, customer_email,
                     booking_date, booking_time, number_of_guests, area_id, table_id,
                     notes, status)
                 VALUES
                    (:user_id, :customer_name, :customer_phone, :customer_email,
                     :booking_date, :booking_time, :number_of_guests, :area_id, :table_id,
                     :notes, :status)'
            );
            $insertBooking->execute([
                'user_id' => $booking['user_id'],
                'customer_name' => $booking['customer_name'],
                'customer_phone' => $booking['customer_phone'],
                'customer_email' => $booking['customer_email'],
                'booking_date' => $booking['booking_date'],
                'booking_time' => $booking['booking_time'],
                'number_of_guests' => $booking['number_of_guests'],
                'area_id' => $table['area_id'],
                'table_id' => $table['id'],
                'notes' => $booking['notes'],
                'status' => 'pending',
            ]);
            $bookingId = (int) $this->db->lastInsertId();

            $historyStatement = $this->db->prepare(
                'INSERT INTO booking_status_histories
                    (booking_id, old_status, new_status, changed_by, note)
                 VALUES (:booking_id, :old_status, :new_status, :changed_by, :note)'
            );
            $historyStatement->execute([
                'booking_id' => $bookingId,
                'old_status' => null,
                'new_status' => 'pending',
                'changed_by' => $booking['user_id'],
                'note' => null,
            ]);

            $detailStatement = $this->db->prepare(
                'INSERT INTO booking_details
                    (booking_id, dish_id, quantity, unit_price, note)
                 VALUES (:booking_id, :dish_id, :quantity, :unit_price, :note)'
            );
            foreach ($cart as $dishId => $quantity) {
                $detailStatement->execute([
                    'booking_id' => $bookingId,
                    'dish_id' => $dishId,
                    'quantity' => $quantity,
                    'unit_price' => $orderableDishes[$dishId]['price'],
                    'note' => $dishNotes[$dishId] ?? null,
                ]);
            }

            $this->db->commit();

            return $bookingId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function allForCustomer(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT b.id, b.customer_name, b.booking_date, b.booking_time, b.number_of_guests,
                    b.status, b.created_at, t.code AS table_code, t.name AS table_name,
                    a.name AS area_name
             FROM bookings b
             LEFT JOIN restaurant_tables t ON t.id = b.table_id
             LEFT JOIN dining_areas a ON a.id = b.area_id
             WHERE b.user_id = :user_id
             ORDER BY b.booking_date DESC, b.booking_time DESC, b.id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function allForStaff(?string $date = null, ?string $status = null): array
    {
        $conditions = [];
        $parameters = [];
        if ($date !== null) {
            $conditions[] = 'b.booking_date = :booking_date';
            $parameters['booking_date'] = $date;
        }
        if ($status !== null) {
            $conditions[] = 'b.status = :status';
            $parameters['status'] = $status;
        }

        $sql = 'SELECT b.id, b.user_id, b.customer_name, b.customer_phone, b.customer_email,
                       b.booking_date, b.booking_time, b.number_of_guests, b.notes, b.status,
                       b.created_at, t.code AS table_code, t.name AS table_name, a.name AS area_name
                FROM bookings b
                LEFT JOIN restaurant_tables t ON t.id = b.table_id
                LEFT JOIN dining_areas a ON a.id = b.area_id'
            . ($conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions))
            . ' ORDER BY b.booking_date ASC, b.booking_time ASC, b.id ASC
                LIMIT 100';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function preordersForBookings(array $bookingIds): array
    {
        $bookingIds = array_values(array_unique(array_filter(
            array_map('intval', $bookingIds),
            static fn (int $id): bool => $id > 0
        )));
        if ($bookingIds === []) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach ($bookingIds as $index => $bookingId) {
            $placeholder = ':booking_id_' . $index;
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = $bookingId;
        }

        $statement = $this->db->prepare(
            'SELECT bd.booking_id, d.name AS dish_name, bd.quantity, bd.note
             FROM booking_details bd
             INNER JOIN dishes d ON d.id = bd.dish_id
             WHERE bd.booking_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY bd.booking_id ASC, bd.id ASC'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findForCustomer(int $bookingId, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, t.code AS table_code, t.name AS table_name,
                    a.name AS area_name
             FROM bookings b
             LEFT JOIN restaurant_tables t ON t.id = b.table_id
             LEFT JOIN dining_areas a ON a.id = b.area_id
             WHERE b.id = :id AND b.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['id' => $bookingId, 'user_id' => $userId]);
        $booking = $statement->fetch();

        return $booking === false ? null : $booking;
    }

    public function details(int $bookingId): array
    {
        $statement = $this->db->prepare(
            'SELECT bd.quantity, bd.unit_price, bd.note, d.name AS dish_name
             FROM booking_details bd
             INNER JOIN dishes d ON d.id = bd.dish_id
             WHERE bd.booking_id = :booking_id
             ORDER BY bd.id ASC'
        );
        $statement->execute(['booking_id' => $bookingId]);

        return $statement->fetchAll();
    }

    public function histories(int $bookingId): array
    {
        $statement = $this->db->prepare(
            'SELECT h.old_status, h.new_status, h.note, h.created_at, u.name AS changed_by_name
             FROM booking_status_histories h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.booking_id = :booking_id
             ORDER BY h.id ASC'
        );
        $statement->execute(['booking_id' => $bookingId]);

        return $statement->fetchAll();
    }

    public function transitionStatus(
        int $bookingId,
        string $newStatus,
        int $changedBy,
        ?int $expectedOwnerId = null,
        ?string $note = null
    ): void {
        $this->db->beginTransaction();

        try {
            $tableIdStatement = $this->db->prepare(
                'SELECT table_id FROM bookings WHERE id = :id LIMIT 1'
            );
            $tableIdStatement->execute(['id' => $bookingId]);
            $tableId = $tableIdStatement->fetchColumn();
            if ($tableId === false || $tableId === null) {
                throw new DomainException('booking_not_found');
            }

            $tableStatement = $this->db->prepare(
                'SELECT id, status FROM restaurant_tables WHERE id = :id FOR UPDATE'
            );
            $tableStatement->execute(['id' => $tableId]);
            $table = $tableStatement->fetch();
            if ($table === false) {
                throw new DomainException('booking_not_found');
            }

            $bookingStatement = $this->db->prepare(
                'SELECT id, user_id, table_id, booking_date, status
                 FROM bookings WHERE id = :id FOR UPDATE'
            );
            $bookingStatement->execute(['id' => $bookingId]);
            $booking = $bookingStatement->fetch();
            if (
                $booking === false
                || (int) $booking['table_id'] !== (int) $table['id']
                || ($expectedOwnerId !== null && (int) $booking['user_id'] !== $expectedOwnerId)
            ) {
                throw new DomainException('booking_not_found');
            }

            $allowedTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['seated', 'cancelled', 'no_show'],
                'seated' => ['completed'],
                'completed' => [],
                'cancelled' => [],
                'no_show' => [],
            ];
            if (!in_array($newStatus, $allowedTransitions[$booking['status']] ?? [], true)) {
                throw new DomainException('invalid_transition');
            }

            $isToday = $booking['booking_date'] === date('Y-m-d');
            if ($newStatus === 'confirmed' && $isToday
                && !in_array($table['status'], ['empty', 'reserved'], true)) {
                throw new DomainException('table_busy');
            }
            if ($newStatus === 'seated' && !$isToday) {
                throw new DomainException('booking_not_today');
            }
            if ($newStatus === 'seated'
                && !in_array($table['status'], ['empty', 'reserved'], true)) {
                throw new DomainException('table_busy');
            }

            $updateBooking = $this->db->prepare(
                'UPDATE bookings
                 SET status = :status, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $updateBooking->execute(['status' => $newStatus, 'id' => $bookingId]);

            $historyStatement = $this->db->prepare(
                'INSERT INTO booking_status_histories
                    (booking_id, old_status, new_status, changed_by, note)
                 VALUES (:booking_id, :old_status, :new_status, :changed_by, :note)'
            );
            $historyStatement->execute([
                'booking_id' => $bookingId,
                'old_status' => $booking['status'],
                'new_status' => $newStatus,
                'changed_by' => $changedBy,
                'note' => $note,
            ]);

            if ($isToday && $newStatus === 'confirmed' && $table['status'] === 'empty') {
                $this->setTableStatus((int) $tableId, 'reserved');
            } elseif ($newStatus === 'seated') {
                $this->setTableStatus((int) $tableId, 'in_use');
            } elseif ($newStatus === 'completed') {
                $this->setTableStatus((int) $tableId, 'cleaning');
            } elseif (
                $isToday
                && in_array($newStatus, ['cancelled', 'no_show'], true)
                && $table['status'] === 'reserved'
            ) {
                $this->releaseTableIfNoOtherBooking((int) $tableId, $booking);
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    private function lockOrderableDishes(array $dishIds): array
    {
        if ($dishIds === []) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach (array_values($dishIds) as $index => $dishId) {
            $placeholder = ':dish_id_' . $index;
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = (int) $dishId;
        }

        $statement = $this->db->prepare(
            'SELECT d.id, d.price
             FROM dishes d
             INNER JOIN categories c ON c.id = d.category_id
             WHERE d.id IN (' . implode(', ', $placeholders) . ')
               AND d.is_visible = 1 AND d.is_available = 1 AND c.is_visible = 1
             FOR UPDATE'
        );
        $statement->execute($parameters);

        $dishes = [];
        foreach ($statement->fetchAll() as $dish) {
            $dishes[(int) $dish['id']] = $dish;
        }

        return $dishes;
    }

    private function releaseTableIfNoOtherBooking(int $tableId, array $booking): void
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM bookings
             WHERE table_id = :table_id
               AND booking_date = :booking_date
               AND status IN (:pending_status, :confirmed_status)
               AND id <> :booking_id
             LIMIT 1'
        );
        $statement->execute([
            'table_id' => $tableId,
            'booking_date' => $booking['booking_date'],
            'pending_status' => 'pending',
            'confirmed_status' => 'confirmed',
            'booking_id' => $booking['id'],
        ]);

        $this->setTableStatus(
            $tableId,
            $statement->fetchColumn() === false ? 'empty' : 'reserved'
        );
    }

    private function setTableStatus(int $tableId, string $status): void
    {
        $statement = $this->db->prepare(
            'UPDATE restaurant_tables
             SET status = :status, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'id' => $tableId]);
    }

    private function bookingDateTime(string $date, string $time): DateTimeImmutable
    {
        return new DateTimeImmutable(
            $date . ' ' . $time,
            new DateTimeZone(date_default_timezone_get())
        );
    }
}
