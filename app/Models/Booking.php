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
        $isFutureDate = $date > (new DateTimeImmutable('today'))->format('Y-m-d');

        $statement = $this->db->prepare(
            'SELECT t.id, t.code, t.name, t.capacity, t.status, t.is_vip, t.table_fee, a.name AS area_name
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
                'SELECT id, area_id, capacity, status, is_vip, table_fee
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

            $tableFee = self::resolveTableFee($table['table_fee'] ?? 0, !empty($table['is_vip']));
            $foodSubtotal = 0.0;
            foreach ($cart as $dishId => $quantity) {
                $foodSubtotal += (float) $orderableDishes[(int) $dishId]['price'] * (int) $quantity;
            }
            $voucher = (new Voucher($this->db))->lockValidForBooking(
                (string) ($booking['voucher_code'] ?? ''),
                $foodSubtotal
            );
            $depositAmount = !empty($booking['pay_deposit'])
                ? round(($tableFee + $foodSubtotal) * 0.25)
                : 0.0;

            $insertBooking = $this->db->prepare(
                'INSERT INTO bookings
                    (user_id, customer_name, customer_phone, customer_email,
                     booking_date, booking_time, number_of_guests, area_id, table_id,
                     deposit_amount, voucher_code, voucher_discount_type,
                     voucher_discount_value, voucher_min_order_amount, voucher_max_discount_amount,
                     notes, status)
                 VALUES
                    (:user_id, :customer_name, :customer_phone, :customer_email,
                     :booking_date, :booking_time, :number_of_guests, :area_id, :table_id,
                     :deposit_amount, :voucher_code, :voucher_discount_type,
                     :voucher_discount_value, :voucher_min_order_amount, :voucher_max_discount_amount,
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
                'deposit_amount' => $depositAmount,
                'voucher_code' => $voucher['code'] ?? null,
                'voucher_discount_type' => $voucher['discount_type'] ?? null,
                'voucher_discount_value' => $voucher['discount_value'] ?? null,
                'voucher_min_order_amount' => $voucher['min_order_amount'] ?? null,
                'voucher_max_discount_amount' => $voucher['max_discount_amount'] ?? null,
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
                       b.created_at, t.id AS table_id, t.code AS table_code,
                       t.name AS table_name, t.table_fee, t.is_vip, a.name AS area_name
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

            if (in_array($newStatus, ['cancelled', 'no_show'], true) && !empty($booking['voucher_code'])) {
                $releaseVoucher = $this->db->prepare(
                    'UPDATE vouchers SET used_count = GREATEST(used_count - 1, 0) WHERE code = :code'
                );
                $releaseVoucher->execute(['code' => $booking['voucher_code']]);
            }

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
        $minutes = $overrideLateMinutes;
        if ($minutes === null) {
            $stmt = $this->db->prepare("SELECT `value` FROM restaurant_settings WHERE `key` = 'auto_release_late_minutes' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            $minutes = $val !== false ? (int)$val : 30;
        }

        // Tìm các đặt bàn hôm nay có status = 'confirmed', giờ đến + minutes < thời gian hiện tại
        $findStmt = $this->db->prepare(
            "SELECT b.id, b.table_id, b.booking_date, b.booking_time, b.voucher_code
             FROM bookings b
             WHERE b.status = 'confirmed'
               AND b.table_id IS NOT NULL
               AND TIMESTAMP(b.booking_date, b.booking_time) < DATE_SUB(NOW(), INTERVAL :late_mins MINUTE)"
        );
        $findStmt->execute(['late_mins' => $minutes]);
        $lateBookings = $findStmt->fetchAll(\PDO::FETCH_ASSOC);

        $releasedCount = 0;
        foreach ($lateBookings as $lb) {
            // Chuyển sang no_show và giải phóng bàn về empty
            $upStmt = $this->db->prepare("UPDATE bookings SET status = 'no_show', updated_at = NOW() WHERE id = :id");
            $upStmt->execute(['id' => $lb['id']]);

            if (!empty($lb['voucher_code'])) {
                $releaseVoucher = $this->db->prepare(
                    'UPDATE vouchers SET used_count = GREATEST(used_count - 1, 0) WHERE code = :code'
                );
                $releaseVoucher->execute(['code' => $lb['voucher_code']]);
            }

            if (!empty($lb['table_id'])) {
                $this->releaseTableIfNoOtherBooking((int)$lb['table_id'], $lb);
            }
            $releasedCount++;
        }

        return $releasedCount;
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
            if (!in_array($booking['status'], ['pending', 'confirmed', 'seated'], true)) {
                throw new DomainException('invalid_transition');
            }

            $tableId = (int) $booking['table_id'];
            $tableLock = $this->db->prepare('SELECT status, table_fee, is_vip FROM restaurant_tables WHERE id = :id FOR UPDATE');
            $tableLock->execute(['id' => $tableId]);
            $table = $tableLock->fetch(\PDO::FETCH_ASSOC);
            if ($table === false || $table['status'] === 'locked') {
                throw new DomainException('table_unavailable');
            }
            $tableStatus = $table['status'];

            $ordCheck = $this->db->prepare(
                "SELECT id FROM orders WHERE booking_id = :bid AND status NOT IN ('completed', 'cancelled') LIMIT 1 FOR UPDATE"
            );
            $ordCheck->execute(['bid' => $bookingId]);
            $existingOrderId = $ordCheck->fetchColumn();
            if ($existingOrderId !== false) {
                $this->setTableStatus($tableId, 'in_use');
                $this->markBookingSeated($booking, $bookingId, $userId);
                $this->db->commit();

                return ['order_id' => (int) $existingOrderId, 'table_id' => $tableId];
            }
            if (in_array($tableStatus, ['in_use', 'serving', 'awaiting_payment'], true)) {
                throw new DomainException('table_busy');
            }

            $tableFee = self::resolveTableFee($table['table_fee'] ?? 0, !empty($table['is_vip']));
            $setupFee = 0.0;
            if (!empty($booking['combo_id'])) {
                $comboPrice = $this->db->prepare('SELECT price FROM combo_setups WHERE id = :id');
                $comboPrice->execute(['id' => $booking['combo_id']]);
                $setupFee = (float) ($comboPrice->fetchColumn() ?: 0);
            }
            $dStmt = $this->db->prepare(
                'SELECT bd.*, d.name AS dish_name FROM booking_details bd
                 INNER JOIN dishes d ON d.id = bd.dish_id WHERE bd.booking_id = :bid'
            );
            $dStmt->execute(['bid' => $bookingId]);
            $items = $dStmt->fetchAll(\PDO::FETCH_ASSOC);

            $foodTotal = 0.0;
            foreach ($items as $item) {
                $foodTotal += (float) $item['unit_price'] * (int) $item['quantity'];
            }
            $voucher = empty($booking['voucher_code']) ? null : [
                'code' => $booking['voucher_code'],
                'discount_type' => $booking['voucher_discount_type'],
                'discount_value' => $booking['voucher_discount_value'],
                'min_order_amount' => $booking['voucher_min_order_amount'],
                'max_discount_amount' => $booking['voucher_max_discount_amount'],
            ];
            $discount = Voucher::calculateDiscount($voucher, $foodTotal);
            $subtotal = $foodTotal + $tableFee + $setupFee;
            $billableSubtotal = max(0.0, $subtotal - $discount);
            $serviceCharge = round($billableSubtotal * 0.05, 2);
            $tax = round(($billableSubtotal + $serviceCharge) * 0.08, 2);
            $grandTotal = $billableSubtotal + $serviceCharge + $tax;

            $insertOrder = $this->db->prepare(
                "INSERT INTO orders
                    (booking_id, table_id, created_by, customer_name, status, payment_status,
                     total_amount, table_fee, setup_fee, discount, voucher_code, service_charge, tax, grand_total,
                     notes, created_at, updated_at)
                 VALUES (:bid, :tid, :uid, :cname, 'confirmed', 'unpaid', :total, :tfee, :sfee,
                         :discount, :voucher_code, :service, :tax, :grand_total, :notes, NOW(), NOW())"
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
                'voucher_code' => $booking['voucher_code'] ?? null,
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

            $this->setTableStatus($tableId, 'in_use');
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
