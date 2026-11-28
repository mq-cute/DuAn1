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

    public static function resolveTableFee(float|string|null $configuredFee, bool $isVip): float
    {
        $fee = (float) $configuredFee;

        return $fee > 0 ? $fee : ($isVip ? 200000.0 : 100000.0);
    }

    public function availableTables(string $date, string $time, int $guestCount): array
    {
        $start = $this->bookingDateTime($date, $time);
        $end = $start->modify('+' . self::DURATION_MINUTES . ' minutes');

        $statement = $this->db->prepare(
            'SELECT t.id, t.code, t.name, t.capacity, t.status, t.is_vip, t.table_fee,
                    t.room_id, r.name AS room_name, r.code AS room_code,
                    a.id AS area_id, a.name AS area_name
             FROM restaurant_tables t
             INNER JOIN dining_areas a ON a.id = t.area_id
             LEFT JOIN rooms r ON r.id = t.room_id
             WHERE t.is_active = 1
               AND r.is_active = 1
               AND t.status <> :locked_status
               AND NOT EXISTS (
                   SELECT 1 FROM bookings b
                   LEFT JOIN booking_tables bt ON bt.booking_id = b.id
                   WHERE b.status IN (:pending_status, :confirmed_status, :seated_status, :waiting_status)
                     AND (b.table_id = t.id OR bt.table_id = t.id)
                     AND TIMESTAMP(b.booking_date, b.booking_time) < :candidate_end
                     AND DATE_ADD(TIMESTAMP(b.booking_date, b.booking_time),
                                  INTERVAL ' . self::DURATION_MINUTES . ' MINUTE) > :candidate_start
               )
             ORDER BY a.sort_order ASC, t.capacity ASC, t.code ASC'
        );
        $statement->execute([
            'locked_status' => 'locked',
            'pending_status' => 'pending',
            'confirmed_status' => 'confirmed',
            'seated_status' => 'seated',
            'waiting_status' => 'waiting_pending',
            'candidate_start' => $start->format('Y-m-d H:i:s'),
            'candidate_end' => $end->format('Y-m-d H:i:s'),
        ]);

        $tables = $statement->fetchAll();
        $tablesByRoom = [];
        foreach ($tables as $table) {
            $roomId = (int) ($table['room_id'] ?? 0);
            if ($roomId > 0) {
                $tablesByRoom[$roomId][] = $table;
            }
        }

        $rooms = [];
        foreach ($tablesByRoom as $roomId => $roomTables) {
            usort($roomTables, static fn(array $a, array $b): int =>
                ((int) $a['capacity'] <=> (int) $b['capacity']) ?: strcmp((string) $a['code'], (string) $b['code'])
            );
            $feasible = $this->findTableCombination($roomTables, $guestCount);
            if ($feasible !== []) {
                $rooms[(int) $roomId] = [
                    'room_id' => $roomId,
                    'room_name' => $roomTables[0]['room_name'],
                    'room_code' => $roomTables[0]['room_code'],
                    'area_id' => (int) $roomTables[0]['area_id'],
                    'area_name' => $roomTables[0]['area_name'],
                    'tables' => $roomTables,
                ];
            }
        }

        return array_values($rooms);
    }

    public function availableTablesForRoom(string $date, string $time, int $roomId): array
    {
        $all = $this->availableTables($date, $time, 1);
        foreach ($all as $room) {
            if ((int) $room['room_id'] === $roomId) {
                return $room['tables'];
            }
        }
        return [];
    }

    private function findTableCombination(array $tables, int $guestCount): array
    {
        $best = [];
        $bestCapacity = PHP_INT_MAX;
        $bestCount = PHP_INT_MAX;
        $total = count($tables);
        for ($mask = 1; $mask < (1 << $total); $mask++) {
            $capacity = 0;
            $count = 0;
            $selected = [];
            for ($i = 0; $i < $total; $i++) {
                if (($mask & (1 << $i)) !== 0) {
                    $capacity += (int) $tables[$i]['capacity'];
                    $count++;
                    $selected[] = $tables[$i];
                }
            }
            if (
                $capacity >= $guestCount
                && ($capacity < $bestCapacity || ($capacity === $bestCapacity && $count < $bestCount))
            ) {
                $best = $selected;
                $bestCapacity = $capacity;
                $bestCount = $count;
            }
        }
        return $best;
    }

    public function createReservation(array $booking, array $cart, array $dishNotes): int
    {
        $this->db->beginTransaction();

        try {
            $start = $this->bookingDateTime($booking['booking_date'], $booking['booking_time']);
            $end = $start->modify('+' . self::DURATION_MINUTES . ' minutes');
            $tableIds = array_values(array_unique(array_map('intval', $booking['table_ids'] ?? [$booking['table_id'] ?? 0])));
            $tableIds = array_values(array_filter($tableIds, static fn(int $id): bool => $id > 0));
            if ($tableIds === []) {
                throw new DomainException('table_unavailable');
            }

            $roomId = (int) ($booking['room_id'] ?? 0);
            $capacity = 0;
            $tableFee = 0.0;
            $tables = [];
            $tableStatement = $this->db->prepare(
                'SELECT t.id, t.area_id, t.room_id, t.capacity, t.status, t.is_active,
                        t.is_vip, t.table_fee, r.is_active AS room_is_active
                 FROM restaurant_tables t
                 LEFT JOIN rooms r ON r.id = t.room_id
                 WHERE t.id = :id
                 FOR UPDATE'
            );
            foreach ($tableIds as $tableId) {
                $tableStatement->execute(['id' => $tableId]);
                $table = $tableStatement->fetch();
                if (
                    $table === false
                    || (int) $table['is_active'] !== 1
                    || (int) ($table['room_is_active'] ?? 0) !== 1
                    || $table['status'] === 'locked'
                    || ($roomId > 0 && (int) $table['room_id'] !== $roomId)
                    || ($booking['booking_date'] === date('Y-m-d')
                        && !in_array($table['status'], ['empty', 'reserved'], true))
                ) {
                    throw new DomainException('table_unavailable');
                }

                $overlapStatement = $this->db->prepare(
                    'SELECT b.id
                     FROM bookings b
                     LEFT JOIN booking_tables bt ON bt.booking_id = b.id
                     WHERE b.status IN (:pending_status, :confirmed_status, :seated_status, :waiting_status)
                       AND (b.table_id = :booking_table_id OR bt.table_id = :linked_table_id)
                       AND TIMESTAMP(b.booking_date, b.booking_time) < :candidate_end
                       AND DATE_ADD(TIMESTAMP(b.booking_date, b.booking_time),
                                    INTERVAL ' . self::DURATION_MINUTES . ' MINUTE) > :candidate_start
                     LIMIT 1
                     FOR UPDATE'
                );
                $overlapStatement->execute([
                    'booking_table_id' => $tableId,
                    'linked_table_id' => $tableId,
                    'pending_status' => 'pending',
                    'confirmed_status' => 'confirmed',
                    'seated_status' => 'seated',
                    'waiting_status' => 'waiting_pending',
                    'candidate_start' => $start->format('Y-m-d H:i:s'),
                    'candidate_end' => $end->format('Y-m-d H:i:s'),
                ]);
                if ($overlapStatement->fetchColumn() !== false) {
                    throw new DomainException('table_unavailable');
                }

                $tables[] = $table;
                $capacity += (int) $table['capacity'];
                $tableFee += self::resolveTableFee($table['table_fee'] ?? 0, !empty($table['is_vip']));
            }
            if ($capacity < (int) $booking['number_of_guests']) {
                throw new DomainException('insufficient_capacity');
            }
            if (count($tables) !== count($tableIds)) {
                throw new DomainException('table_unavailable');
            }
            $areaId = (int) $tables[0]['area_id'];
            $resolvedRoomId = (int) $tables[0]['room_id'];
            foreach ($tables as $table) {
                if ((int) $table['room_id'] !== $resolvedRoomId) {
                    throw new DomainException('table_room_mismatch');
                }
            }

            $orderableDishes = $this->lockOrderableDishes(array_keys($cart));
            if (count($orderableDishes) !== count($cart)) {
                throw new DomainException('dish_unavailable');
            }

            $foodSubtotal = 0.0;
            foreach ($cart as $dishId => $quantity) {
                $foodSubtotal += (float) $orderableDishes[(int) $dishId]['price'] * (int) $quantity;
            }
            $depositAmount = !empty($booking['pay_deposit'])
                ? round(($tableFee + $foodSubtotal) * 0.25)
                : 0.0;
            $estimatedTotal = $tableFee + $foodSubtotal;

            $insertBooking = $this->db->prepare(
                'INSERT INTO bookings
                    (user_id, customer_name, customer_phone, customer_email,
                     booking_date, booking_time, number_of_guests, area_id, room_id, table_id,
                     deposit_amount, voucher_code, voucher_discount_type,
                     voucher_discount_value, voucher_min_order_amount, voucher_max_discount_amount,
                     estimated_total, notes, status)
                 VALUES
                    (:user_id, :customer_name, :customer_phone, :customer_email,
                     :booking_date, :booking_time, :number_of_guests, :area_id, :room_id, :table_id,
                     :deposit_amount, :voucher_code, :voucher_discount_type,
                     :voucher_discount_value, :voucher_min_order_amount, :voucher_max_discount_amount,
                     :estimated_total, :notes, :status)'
            );
            $insertBooking->execute([
                'user_id' => $booking['user_id'],
                'customer_name' => $booking['customer_name'],
                'customer_phone' => $booking['customer_phone'],
                'customer_email' => $booking['customer_email'],
                'booking_date' => $booking['booking_date'],
                'booking_time' => $booking['booking_time'],
                'number_of_guests' => $booking['number_of_guests'],
                'area_id' => $areaId,
                'room_id' => $resolvedRoomId,
                'table_id' => $tables[0]['id'],
                'deposit_amount' => $depositAmount,
                'voucher_code' => null,
                'voucher_discount_type' => null,
                'voucher_discount_value' => null,
                'voucher_min_order_amount' => null,
                'voucher_max_discount_amount' => null,
                'estimated_total' => $estimatedTotal,
                'notes' => $booking['notes'],
                'status' => 'pending',
            ]);
            $bookingId = (int) $this->db->lastInsertId();

            $bookingTableStatement = $this->db->prepare(
                'INSERT IGNORE INTO booking_tables
                    (booking_id, table_id, table_fee_snapshot)
                 VALUES (:booking_id, :table_id, :table_fee_snapshot)'
            );
            foreach ($tables as $table) {
                $snapshotFee = self::resolveTableFee($table['table_fee'] ?? 0, !empty($table['is_vip']));
                $bookingTableStatement->execute([
                    'booking_id' => $bookingId,
                    'table_id' => (int) $table['id'],
                    'table_fee_snapshot' => $snapshotFee,
                ]);
            }

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

    public function allForUser(int $userId): array
    {
        return $this->allForCustomer($userId);
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
                       b.deposit_amount, b.deposit_received_at, b.estimated_total,
               b.room_id, r.name AS room_name,
                       b.created_at, t.id AS table_id, t.code AS table_code,
                       t.name AS table_name, t.table_fee, t.is_vip, a.name AS area_name
                FROM bookings b
                LEFT JOIN restaurant_tables t ON t.id = b.table_id
                LEFT JOIN dining_areas a ON a.id = b.area_id
                LEFT JOIN rooms r ON r.id = b.room_id'
            . ($conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions))
            . ' ORDER BY b.booking_date ASC, b.booking_time ASC, b.id ASC
                LIMIT 100';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findForStaff(int $bookingId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, t.code AS table_code, t.name AS table_name,
                    t.table_fee, t.is_vip, a.name AS area_name,
                    c.name AS combo_name, c.price AS combo_price
             FROM bookings b
             LEFT JOIN restaurant_tables t ON t.id = b.table_id
             LEFT JOIN dining_areas a ON a.id = b.area_id
             LEFT JOIN combo_setups c ON c.id = b.combo_id
             WHERE b.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $bookingId]);
        $booking = $statement->fetch();

        return $booking === false ? null : $booking;
    }

    public function recordLateContact(int $bookingId, int $staffId, string $note): void
    {
        $note = trim($note);
        if ($note === '' || mb_strlen($note) > 2000) {
            throw new DomainException('invalid_late_contact');
        }

        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'SELECT id, status FROM bookings WHERE id = :id FOR UPDATE'
            );
            $statement->execute(['id' => $bookingId]);
            $booking = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($booking === false) {
                throw new DomainException('booking_not_found');
            }

            $update = $this->db->prepare(
                'UPDATE bookings
                 SET late_contacted_at = COALESCE(late_contacted_at, CURRENT_TIMESTAMP),
                     late_contact_note = :note,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $update->execute(['note' => $note, 'id' => $bookingId]);

            $history = $this->db->prepare(
                'INSERT INTO booking_status_histories
                    (booking_id, old_status, new_status, changed_by, note)
                 VALUES (:booking_id, :old_status, :old_status, :changed_by, :note)'
            );
            $history->execute([
                'booking_id' => $bookingId,
                'old_status' => $booking['status'],
                'changed_by' => $staffId,
                'note' => 'Ghi nhận liên hệ khách đến muộn: ' . $note,
            ]);
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function markDepositReceived(int $bookingId, int $staffId): void
    {
        $statement = $this->db->prepare(
            'UPDATE bookings
             SET deposit_received_at = COALESCE(deposit_received_at, CURRENT_TIMESTAMP),
                 deposit_received_by = COALESCE(deposit_received_by, :staff_id),
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND deposit_amount > 0 AND status NOT IN ("cancelled", "no_show")'
        );
        $statement->execute(['id' => $bookingId, 'staff_id' => $staffId]);
        if ($statement->rowCount() === 0) {
            $check = $this->db->prepare(
                'SELECT deposit_received_at FROM bookings WHERE id = :id AND deposit_amount > 0 AND status NOT IN ("cancelled", "no_show")'
            );
            $check->execute(['id' => $bookingId]);
            if ($check->fetchColumn() === false) {
                throw new DomainException('deposit_not_receivable');
            }
        }
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

    public function tablesForBooking(int $bookingId): array
    {
        $statement = $this->db->prepare(
            'SELECT t.id, t.code, t.name, t.capacity, t.status,
                    COALESCE(bt.table_fee_snapshot, t.table_fee) AS table_fee_snapshot
             FROM booking_tables bt
             INNER JOIN restaurant_tables t ON t.id = bt.table_id
             WHERE bt.booking_id = :booking_id
             ORDER BY t.code ASC'
        );
        $statement->execute(['booking_id' => $bookingId]);
        $tables = $statement->fetchAll();
        if ($tables !== []) {
            return $tables;
        }

        $statement = $this->db->prepare(
            'SELECT t.id, t.code, t.name, t.capacity, t.status,
                    CASE WHEN t.table_fee > 0 THEN t.table_fee
                         ELSE (CASE WHEN t.is_vip = 1 THEN 200000 ELSE 100000 END)
                    END AS table_fee_snapshot
             FROM bookings b
             INNER JOIN restaurant_tables t ON t.id = b.table_id
             WHERE b.id = :booking_id'
        );
        $statement->execute(['booking_id' => $bookingId]);
        $legacy = $statement->fetchAll();
        return $legacy;
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
                'SELECT id, user_id, table_id, booking_date, status, voucher_code
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
                'waiting_pending' => ['seated', 'cancelled', 'no_show'],
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
                 SET status = :status,
                     actual_arrival_at = CASE WHEN :new_status = "seated" THEN COALESCE(actual_arrival_at, CURRENT_TIMESTAMP) ELSE actual_arrival_at END,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $updateBooking->execute([
                'status' => $newStatus,
                'new_status' => $newStatus,
                'id' => $bookingId,
            ]);


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

    public function autoReleaseLateBookings(?int $overrideLateMinutes = null): int
    {
        $minutes = $overrideLateMinutes ?? 15;
        $findStmt = $this->db->prepare(
            "SELECT b.id, b.table_id, b.booking_date, b.booking_time
             FROM bookings b
             WHERE b.status IN ('pending', 'confirmed')
               AND TIMESTAMP(b.booking_date, b.booking_time) < DATE_SUB(NOW(), INTERVAL :late_mins MINUTE)"
        );
        $findStmt->execute(['late_mins' => $minutes]);
        $lateBookings = $findStmt->fetchAll(\PDO::FETCH_ASSOC);

        $updatedCount = 0;
        foreach ($lateBookings as $lb) {
            $this->db->beginTransaction();
            try {
                $lock = $this->db->prepare(
                    "SELECT id, status, late_hold_expires_at
                     FROM bookings WHERE id = :id FOR UPDATE"
                );
                $lock->execute(['id' => (int)$lb['id']]);
                $booking = $lock->fetch(\PDO::FETCH_ASSOC);
                if ($booking && in_array($booking['status'], ['pending', 'confirmed'], true)) {
                    $oldStatus = $booking['status'];
                    $upStmt = $this->db->prepare(
                        "UPDATE bookings
                         SET status = 'waiting_pending',
                             late_hold_expires_at = COALESCE(late_hold_expires_at, TIMESTAMP(booking_date, booking_time) + INTERVAL 15 MINUTE),
                             updated_at = NOW()
                         WHERE id = :id"
                    );
                    $upStmt->execute(['id' => (int)$lb['id']]);
                    $history = $this->db->prepare(
                        'INSERT INTO booking_status_histories
                            (booking_id, old_status, new_status, changed_by, note)
                         VALUES (:booking_id, :old_status, :new_status, NULL, :note)'
                    );
                    $history->execute([
                        'booking_id' => (int)$lb['id'],
                        'old_status' => $oldStatus,
                        'new_status' => 'waiting_pending',
                        'note' => 'Quá 15 phút giữ bàn; chuyển sang chờ xử lý, không hủy hoặc giải phóng bàn.',
                    ]);
                    $updatedCount++;
                }
                $this->db->commit();
            } catch (Throwable $exception) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $exception;
            }
        }

        return $updatedCount;
    }

    public function replaceBookingTables(
        int $bookingId,
        array $newTableIds,
        int $userId,
        string $reason,
        ?string $note = null
    ): array {
        $newTableIds = array_values(array_unique(array_map('intval', $newTableIds)));
        $newTableIds = array_values(array_filter($newTableIds, static fn(int $id): bool => $id > 0));
        if ($newTableIds === [] || trim($reason) === '') {
            throw new DomainException('invalid_table_change');
        }

        $this->db->beginTransaction();
        try {
            $bookingStmt = $this->db->prepare(
                'SELECT * FROM bookings WHERE id = :id FOR UPDATE'
            );
            $bookingStmt->execute(['id' => $bookingId]);
            $booking = $bookingStmt->fetch(\PDO::FETCH_ASSOC);
            if ($booking === false) {
                throw new DomainException('booking_not_found');
            }

            $oldTableIds = $this->tablesForBookingLocked($bookingId);
            if ($oldTableIds === []) {
                $oldTableIds = !empty($booking['table_id']) ? [(int)$booking['table_id']] : [];
            }

            $start = $this->bookingDateTime((string)$booking['booking_date'], (string)$booking['booking_time']);
            $end = $start->modify('+' . self::DURATION_MINUTES . ' minutes');
            $capacity = 0;
            $oldFee = 0.0;
            $newFee = 0.0;
            $roomId = (int)$booking['room_id'];

            $tableStmt = $this->db->prepare(
                'SELECT id, room_id, capacity, status, is_active, table_fee, is_vip
                 FROM restaurant_tables WHERE id = :id FOR UPDATE'
            );
            foreach ($newTableIds as $tableId) {
                $tableStmt->execute(['id' => $tableId]);
                $table = $tableStmt->fetch(\PDO::FETCH_ASSOC);
                if (
                    $table === false
                    || (int)$table['is_active'] !== 1
                    || $table['status'] === 'locked'
                    || ($roomId > 0 && (int)$table['room_id'] !== $roomId)
                ) {
                    throw new DomainException('table_busy');
                }
                $capacity += (int)$table['capacity'];
                $newFee += self::resolveTableFee($table['table_fee'] ?? 0, !empty($table['is_vip']));

                $conflict = $this->db->prepare(
                    'SELECT b.id
                     FROM bookings b
                     LEFT JOIN booking_tables bt ON bt.booking_id = b.id
                     WHERE b.id <> :booking_id
                       AND b.status IN ("pending","confirmed","seated","waiting_pending")
                       AND (b.table_id = :booking_table_id OR bt.table_id = :linked_table_id)
                       AND TIMESTAMP(b.booking_date, b.booking_time) < :candidate_end
                       AND DATE_ADD(TIMESTAMP(b.booking_date, b.booking_time),
                                    INTERVAL ' . self::DURATION_MINUTES . ' MINUTE) > :candidate_start
                     LIMIT 1'
                );
                $conflict->execute([
                    'booking_id' => $bookingId,
                    'booking_table_id' => $tableId,
                    'linked_table_id' => $tableId,
                    'candidate_start' => $start->format('Y-m-d H:i:s'),
                    'candidate_end' => $end->format('Y-m-d H:i:s'),
                ]);
                if ($conflict->fetchColumn() !== false) {
                    throw new DomainException('table_busy');
                }
            }

            if ($capacity < (int)$booking['number_of_guests']) {
                throw new DomainException('insufficient_capacity');
            }
            foreach ($oldTableIds as $oldTableId) {
                $oldStmt = $this->db->prepare(
                    'SELECT table_fee, is_vip FROM restaurant_tables WHERE id = :id'
                );
                $oldStmt->execute(['id' => $oldTableId]);
                $oldTable = $oldStmt->fetch(\PDO::FETCH_ASSOC);
                if ($oldTable) {
                    $oldFee += self::resolveTableFee(
                        $oldTable['table_fee'] ?? 0,
                        !empty($oldTable['is_vip'])
                    );
                }
            }

            $delete = $this->db->prepare('DELETE FROM booking_tables WHERE booking_id = :booking_id');
            $delete->execute(['booking_id' => $bookingId]);
            $insert = $this->db->prepare(
                'INSERT INTO booking_tables (booking_id, table_id, table_fee_snapshot)
                 VALUES (:booking_id, :table_id, :table_fee)'
            );
            foreach ($newTableIds as $tableId) {
                $tableStmt->execute(['id' => $tableId]);
                $table = $tableStmt->fetch(\PDO::FETCH_ASSOC);
                $insert->execute([
                    'booking_id' => $bookingId,
                    'table_id' => $tableId,
                    'table_fee' => self::resolveTableFee(
                        $table['table_fee'] ?? 0,
                        !empty($table['is_vip'])
                    ),
                ]);
            }

            $update = $this->db->prepare(
                'UPDATE bookings
                 SET table_id = :table_id,
                     room_id = :room_id,
                     updated_at = NOW()
                 WHERE id = :booking_id'
            );
            $update->execute([
                'table_id' => $newTableIds[0],
                'room_id' => $roomId,
                'booking_id' => $bookingId,
            ]);

            $history = $this->db->prepare(
                'INSERT INTO booking_table_histories
                    (booking_id, room_id, old_table_ids, new_table_ids,
                     old_total_fee, new_total_fee, reason, note, changed_by)
                 VALUES
                    (:booking_id, :room_id, :old_ids, :new_ids,
                     :old_fee, :new_fee, :reason, :note, :changed_by)'
            );
            $history->execute([
                'booking_id' => $bookingId,
                'room_id' => $roomId > 0 ? $roomId : null,
                'old_ids' => json_encode($oldTableIds, JSON_UNESCAPED_UNICODE),
                'new_ids' => json_encode($newTableIds, JSON_UNESCAPED_UNICODE),
                'old_fee' => $oldFee,
                'new_fee' => $newFee,
                'reason' => $reason,
                'note' => $note,
                'changed_by' => $userId,
            ]);

            $this->db->commit();
            return [
                'old_table_ids' => $oldTableIds,
                'new_table_ids' => $newTableIds,
                'old_fee' => $oldFee,
                'new_fee' => $newFee,
            ];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function changeBookingTable(int $bookingId, int $newTableId, ?int $userId = null, ?string $reason = null): bool
    {
        $bStmt = $this->db->prepare("SELECT * FROM bookings WHERE id = :id LIMIT 1");
        $bStmt->execute(['id' => $bookingId]);
        $booking = $bStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$booking) {
            throw new DomainException('booking_not_found');
        }

        $oldTableId = !empty($booking['table_id']) ? (int)$booking['table_id'] : null;

        // Kiểm tra bàn mới có trống không
        $tStmt = $this->db->prepare("SELECT * FROM restaurant_tables WHERE id = :id LIMIT 1");
        $tStmt->execute(['id' => $newTableId]);
        $newTable = $tStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$newTable || in_array($newTable['status'], ['in_use', 'serving', 'locked'])) {
            throw new DomainException('table_busy');
        }

        // Cập nhật booking sang bàn mới
        $up = $this->db->prepare("UPDATE bookings SET table_id = :new_table, updated_at = NOW() WHERE id = :id");
        $up->execute(['new_table' => $newTableId, 'id' => $bookingId]);

        // Đổi trạng thái bàn mới sang reserved nếu booking chưa seated
        if ($booking['status'] === 'seated') {
            $this->setTableStatus($newTableId, 'in_use');
        } else {
            $this->setTableStatus($newTableId, 'reserved');
        }

        // Giải phóng bàn cũ
        if ($oldTableId !== null && $oldTableId !== $newTableId) {
            $this->releaseTableIfNoOtherBooking($oldTableId, $booking);
        }

        // Nếu đã có order đang mở trên bàn cũ, chuyển order sang bàn mới
        $ordUp = $this->db->prepare("UPDATE orders SET table_id = :new_table WHERE booking_id = :bid AND status NOT IN ('completed', 'cancelled')");
        $ordUp->execute(['new_table' => $newTableId, 'bid' => $bookingId]);

        return true;
    }

    private function tablesForBookingLocked(int $bookingId): array
    {
        $statement = $this->db->prepare(
            'SELECT table_id FROM booking_tables
             WHERE booking_id = :booking_id
             ORDER BY id ASC
             FOR UPDATE'
        );
        $statement->execute(['booking_id' => $bookingId]);
        return array_map(
            static fn(array $row): int => (int)$row['table_id'],
            $statement->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function checkinAndOpenTable(int $bookingId, int $userId): array
    {
        $this->db->beginTransaction();
        try {
            $bStmt = $this->db->prepare('SELECT * FROM bookings WHERE id = :id FOR UPDATE');
            $bStmt->execute(['id' => $bookingId]);
            $booking = $bStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$booking) {
                throw new DomainException('booking_not_found');
            }
            if (empty($booking['table_id'])) {
                throw new DomainException('table_not_assigned');
            }
            if (!in_array($booking['status'], ['pending', 'confirmed', 'waiting_pending', 'seated'], true)) {
                throw new DomainException('invalid_transition');
            }

            $tableId = (int) $booking['table_id'];
            $tableIds = $this->tablesForBookingLocked($bookingId);
            if ($tableIds === []) {
                $tableIds = [$tableId];
            }
            $tableLock = $this->db->prepare(
                'SELECT id, room_id, status, is_active, table_fee, is_vip
                 FROM restaurant_tables WHERE id = :id FOR UPDATE'
            );
            $tableFee = 0.0;
            $tableStatus = 'empty';
            foreach ($tableIds as $lockedTableId) {
                $tableLock->execute(['id' => $lockedTableId]);
                $table = $tableLock->fetch(\PDO::FETCH_ASSOC);
                if (
                    $table === false
                    || (int)$table['is_active'] !== 1
                    || $table['status'] === 'locked'
                    || ((int)$table['room_id'] !== (int)$booking['room_id'] && !empty($booking['room_id']))
                    || in_array($table['status'], ['in_use', 'serving', 'awaiting_payment'], true)
                ) {
                    throw new DomainException('table_busy');
                }
                $tableStatus = $table['status'];
                $tableFee += self::resolveTableFee(
                    $table['table_fee'] ?? 0,
                    !empty($table['is_vip'])
                );
            }

            $ordCheck = $this->db->prepare(
                "SELECT id FROM orders WHERE booking_id = :bid AND status NOT IN ('completed', 'cancelled') LIMIT 1 FOR UPDATE"
            );
            $ordCheck->execute(['bid' => $bookingId]);
            $existingOrderId = $ordCheck->fetchColumn();
            if ($existingOrderId !== false) {
                foreach ($tableIds as $lockedTableId) {
                    $this->setTableStatus($lockedTableId, 'in_use');
                }
                $this->markBookingSeated($booking, $bookingId, $userId);
                $this->db->commit();

                return ['order_id' => (int) $existingOrderId, 'table_id' => $tableId];
            }
            if (in_array($tableStatus, ['in_use', 'serving', 'awaiting_payment'], true)) {
                throw new DomainException('table_busy');
            }

            $setupFee = 0.0;
            if (!empty($booking['combo_id'])) {
                $comboPrice = $this->db->prepare('SELECT price FROM combo_setups WHERE id = :id');
                $comboPrice->execute(['id' => $booking['combo_id']]);
                $setupFee = (float) ($comboPrice->fetchColumn() ?: 0);
            }
            $dStmt = $this->db->prepare(
                'SELECT bd.*, d.name AS dish_name FROM booking_details bd
                 INNER JOIN dishes d ON d.id = bd.dish_id WHERE bd.booking_id = :bid
                 ORDER BY bd.id ASC'
            );
            $dStmt->execute(['bid' => $bookingId]);
            $items = $dStmt->fetchAll(\PDO::FETCH_ASSOC);

            $foodTotal = 0.0;
            foreach ($items as $item) {
                $foodTotal += (float) $item['unit_price'] * (int) $item['quantity'];
            }
            $subtotal = $foodTotal + $tableFee + $setupFee;
            $discount = 0.0;
            $billableSubtotal = $subtotal;
            $serviceCharge = round($billableSubtotal * 0.05, 2);
            $tax = round(($billableSubtotal + $serviceCharge) * 0.08, 2);
            $grandTotal = $billableSubtotal + $serviceCharge + $tax;

            $insertOrder = $this->db->prepare(
                "INSERT INTO orders
                    (booking_id, table_id, created_by, customer_name, status, payment_status,
                     total_amount, table_fee, setup_fee, discount, service_charge, tax, grand_total,
                     notes, created_at, updated_at)
                 VALUES (:bid, :tid, :uid, :cname, 'confirmed', 'unpaid', :total, :tfee, :sfee,
                         :discount, :service, :tax, :grand_total, :notes, NOW(), NOW())"
            );
            $insertOrder->execute([
                'bid' => $bookingId,
                'tid' => $tableId,
                'uid' => $userId,
                'cname' => $booking['customer_name'],
                'total' => $foodTotal,
                'tfee' => $tableFee,
                'sfee' => $setupFee,
                'discount' => $discount,
                'service' => $serviceCharge,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'notes' => $booking['notes'] ?? '',
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $insertItem = $this->db->prepare(
                "INSERT INTO order_items
                    (order_id, dish_id, dish_name, quantity, unit_price, subtotal, note, status, created_at, updated_at)
                 VALUES (:order_id, :dish_id, :dish_name, :quantity, :unit_price, :subtotal, :note,
                         'confirmed', NOW(), NOW())"
            );
            foreach ($items as $item) {
                $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                $insertItem->execute([
                    'order_id' => $orderId,
                    'dish_id' => $item['dish_id'],
                    'dish_name' => $item['dish_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $lineTotal,
                    'note' => $item['note'] ?? '',
                ]);
            }

            foreach ($tableIds as $lockedTableId) {
                $this->setTableStatus($lockedTableId, 'in_use');
            }
            $this->markBookingSeated($booking, $bookingId, $userId);
            $this->db->commit();

            return ['order_id' => $orderId, 'table_id' => $tableId];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function markBookingSeated(array $booking, int $bookingId, int $userId): void
    {
        if ($booking['status'] === 'seated') {
            return;
        }

        $update = $this->db->prepare("UPDATE bookings SET status = 'seated', updated_at = NOW() WHERE id = :id");
        $update->execute(['id' => $bookingId]);
        $history = $this->db->prepare(
            'INSERT INTO booking_status_histories
                (booking_id, old_status, new_status, changed_by, note)
             VALUES (:booking_id, :old_status, :new_status, :changed_by, :note)'
        );
        $history->execute([
            'booking_id' => $bookingId,
            'old_status' => $booking['status'],
            'new_status' => 'seated',
            'changed_by' => $userId,
            'note' => 'Đã xác nhận và chuyển đặt bàn vào POS.',
        ]);
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
