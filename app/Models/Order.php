<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use PDO;
use RuntimeException;
use Throwable;

final class Order extends BaseModel
{
    public const SERVICE_CHARGE_RATE = 0.05; // 5%
    public const TAX_RATE = 0.08; // 8%

    public const ORDER_STATUSES = [
        'pending' => 'Chờ tiếp nhận',
        'confirmed' => 'Đã tiếp nhận',
        'preparing' => 'Đang chế biến',
        'ready' => 'Sẵn sàng ra món',
        'served' => 'Đã lên bàn',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];

    public const ITEM_STATUSES = [
        'pending' => 'Chờ tiếp nhận',
        'confirmed' => 'Đã tiếp nhận',
        'preparing' => 'Đang chế biến',
        'ready' => 'Đã chế biến xong',
        'served' => 'Đã phục vụ',
        'cancelled' => 'Đã hủy',
    ];

    public function allOrders(?string $status = null, ?string $date = null): array
    {
        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $conditions[] = 'o.status = :status';
            $params['status'] = $status;
        }

        if ($date !== null && $date !== '') {
            $conditions[] = 'DATE(o.created_at) = :order_date';
            $params['order_date'] = $date;
        }

        $sql = 'SELECT o.*, t.code AS table_code, t.name AS table_name, a.name AS area_name,
                       u.name AS staff_name,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id AND oi.status = "served") AS served_count
                FROM orders o
                INNER JOIN restaurant_tables t ON t.id = o.table_id
                INNER JOIN dining_areas a ON a.id = t.area_id
                LEFT JOIN users u ON u.id = o.created_by'
            . ($conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '')
            . ' ORDER BY o.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, t.code AS table_code, t.name AS table_name, t.capacity AS table_capacity,
                    a.name AS area_name, u.name AS staff_name
             FROM orders o
             INNER JOIN restaurant_tables t ON t.id = o.table_id
             INNER JOIN dining_areas a ON a.id = t.area_id
             LEFT JOIN users u ON u.id = o.created_by
             WHERE o.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order === false ? null : $order;
    }

    public function getActiveOrderByTableId(int $tableId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, t.code AS table_code, t.name AS table_name, a.name AS area_name
             FROM orders o
             INNER JOIN restaurant_tables t ON t.id = o.table_id
             INNER JOIN dining_areas a ON a.id = t.area_id
             WHERE o.table_id = :table_id
               AND o.status NOT IN ('completed', 'cancelled')
               AND o.payment_status = 'unpaid'
             ORDER BY o.id DESC
             LIMIT 1"
        );
        $stmt->execute(['table_id' => $tableId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order === false ? null : $order;
    }

    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, d.image AS dish_image, d.preparation_time, c.name AS category_name
             FROM order_items oi
             INNER JOIN dishes d ON d.id = oi.dish_id
             LEFT JOIN categories c ON c.id = d.category_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.id ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderDetails(int $orderId): ?array
    {
        $order = $this->findById($orderId);
        if ($order === null) {
            return null;
        }

        $order['items'] = $this->getOrderItems($orderId);

        // Fetch invoice if exists
        $invStmt = $this->db->prepare(
            'SELECT * FROM invoices WHERE order_id = :order_id LIMIT 1'
        );
        $invStmt->execute(['order_id' => $orderId]);
        $order['invoice'] = $invStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        // Fetch status histories
        $histStmt = $this->db->prepare(
            'SELECT h.*, u.name AS changed_by_name
             FROM order_status_histories h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.order_id = :order_id
             ORDER BY h.id ASC'
        );
        $histStmt->execute(['order_id' => $orderId]);
        $order['histories'] = $histStmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    public function createOrderAtTable(
        int $tableId,
        ?string $customerName,
        ?int $createdBy,
        array $items,
        ?string $notes = null,
        ?int $bookingId = null
    ): int {
        if ($items === []) {
            throw new DomainException('Danh sách món gọi không được để trống.');
        }

        $this->db->beginTransaction();
        try {
            // Verify table and lock it
            $tblStmt = $this->db->prepare(
                'SELECT id, status, code, name FROM restaurant_tables WHERE id = :id FOR UPDATE'
            );
            $tblStmt->execute(['id' => $tableId]);
            $table = $tblStmt->fetch(PDO::FETCH_ASSOC);
            if ($table === false) {
                throw new DomainException('Bàn không tồn tại.');
            }
            if ($table['status'] === 'locked') {
                throw new DomainException('Bàn đang bị khóa, không thể mở đơn.');
            }

            // Check if there is already an active unpaid order on this table
            $checkStmt = $this->db->prepare(
                "SELECT id FROM orders 
                 WHERE table_id = :table_id 
                   AND status NOT IN ('completed', 'cancelled') 
                   AND payment_status = 'unpaid' 
                 LIMIT 1 
                 FOR UPDATE"
            );
            $checkStmt->execute(['table_id' => $tableId]);
            if ($checkStmt->fetch()) {
                throw new DomainException('Bàn này đang có một đơn gọi món chưa hoàn tất.');
            }

            // Calculate total from dishes in DB
            $preparedItems = $this->validateAndPrepareItems($items);
            $subtotal = 0.0;
            foreach ($preparedItems as $pi) {
                $subtotal += $pi['subtotal'];
            }

            $pricing = $this->getPricingRates();
            $serviceCharge = round($subtotal * $pricing['service_charge_rate']);
            $tax = round($subtotal * $pricing['tax_rate']);
            $grandTotal = $subtotal + $serviceCharge + $tax;

            // Insert into orders
            $orderStmt = $this->db->prepare(
                'INSERT INTO orders (booking_id, table_id, created_by, customer_name, status, payment_status,
                                     total_amount, discount, service_charge, tax, grand_total, notes)
                 VALUES (:booking_id, :table_id, :created_by, :customer_name, "confirmed", "unpaid",
                         :total_amount, 0, :service_charge, :tax, :grand_total, :notes)'
            );
            $orderStmt->execute([
                'booking_id' => $bookingId,
                'table_id' => $tableId,
                'created_by' => $createdBy,
                'customer_name' => $customerName ?: 'Khách tại bàn ' . $table['code'],
                'total_amount' => $subtotal,
                'service_charge' => $serviceCharge,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'notes' => $notes,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            // Insert order items
            $itemInsertStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, dish_id, dish_name, quantity, unit_price, subtotal, note, status)
                 VALUES (:order_id, :dish_id, :dish_name, :quantity, :unit_price, :subtotal, :note, "confirmed")'
            );

            // Generate kitchen ticket
            $ticketNumber = 'KT-' . date('Ymd') . '-' . sprintf('%04d', $orderId);
            $ticketStmt = $this->db->prepare(
                'INSERT INTO kitchen_tickets (order_id, ticket_number, status)
                 VALUES (:order_id, :ticket_number, "pending")'
            );
            $ticketStmt->execute([
                'order_id' => $orderId,
                'ticket_number' => $ticketNumber,
            ]);
            $ticketId = (int) $this->db->lastInsertId();

            $kItemStmt = $this->db->prepare(
                'INSERT INTO kitchen_ticket_items (ticket_id, order_item_id, status)
                 VALUES (:ticket_id, :order_item_id, "confirmed")'
            );

            foreach ($preparedItems as $pi) {
                $itemInsertStmt->execute([
                    'order_id' => $orderId,
                    'dish_id' => $pi['dish_id'],
                    'dish_name' => $pi['dish_name'],
                    'quantity' => $pi['quantity'],
                    'unit_price' => $pi['unit_price'],
                    'subtotal' => $pi['subtotal'],
                    'note' => $pi['note'],
                ]);
                $orderItemId = (int) $this->db->lastInsertId();

                $kItemStmt->execute([
                    'ticket_id' => $ticketId,
                    'order_item_id' => $orderItemId,
                ]);
            }

            // Update table status to 'serving'
            $updateTableStmt = $this->db->prepare(
                'UPDATE restaurant_tables SET status = "serving", updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $updateTableStmt->execute(['id' => $tableId]);

            // Log status history
            $histStmt = $this->db->prepare(
                'INSERT INTO order_status_histories (order_id, old_status, new_status, changed_by, note)
                 VALUES (:order_id, "pending", "confirmed", :changed_by, "Nhân viên tạo đơn gọi món tại bàn")'
            );
            $histStmt->execute([
                'order_id' => $orderId,
                'changed_by' => $createdBy,
            ]);

            $this->db->commit();

            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addItemsToOrder(int $orderId, array $items, ?int $userId = null): void
    {
        if ($items === []) {
            throw new DomainException('Danh sách món gọi thêm không được để trống.');
        }

        $this->db->beginTransaction();
        try {
            $order = $this->findById($orderId);
            if ($order === null || in_array($order['status'], ['completed', 'cancelled'], true)) {
                throw new DomainException('Đơn hàng không hợp lệ hoặc đã kết thúc.');
            }

            $preparedItems = $this->validateAndPrepareItems($items);

            // Generate supplementary kitchen ticket
            $ticketNumber = 'KT-' . date('Ymd') . '-' . sprintf('%04d', $orderId) . '-A' . rand(10, 99);
            $ticketStmt = $this->db->prepare(
                'INSERT INTO kitchen_tickets (order_id, ticket_number, status)
                 VALUES (:order_id, :ticket_number, "pending")'
            );
            $ticketStmt->execute([
                'order_id' => $orderId,
                'ticket_number' => $ticketNumber,
            ]);
            $ticketId = (int) $this->db->lastInsertId();

            $itemInsertStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, dish_id, dish_name, quantity, unit_price, subtotal, note, status)
                 VALUES (:order_id, :dish_id, :dish_name, :quantity, :unit_price, :subtotal, :note, "confirmed")'
            );

            $kItemStmt = $this->db->prepare(
                'INSERT INTO kitchen_ticket_items (ticket_id, order_item_id, status)
                 VALUES (:ticket_id, :order_item_id, "confirmed")'
            );

            foreach ($preparedItems as $pi) {
                $itemInsertStmt->execute([
                    'order_id' => $orderId,
                    'dish_id' => $pi['dish_id'],
                    'dish_name' => $pi['dish_name'],
                    'quantity' => $pi['quantity'],
                    'unit_price' => $pi['unit_price'],
                    'subtotal' => $pi['subtotal'],
                    'note' => $pi['note'],
                ]);
                $orderItemId = (int) $this->db->lastInsertId();

                $kItemStmt->execute([
                    'ticket_id' => $ticketId,
                    'order_item_id' => $orderItemId,
                ]);
            }

            // Recalculate totals
            $this->recalculateOrderTotals($orderId);

            // If order was served, set back to preparing/confirmed
            if ($order['status'] === 'served') {
                $this->db->prepare('UPDATE orders SET status = "confirmed" WHERE id = :id')->execute(['id' => $orderId]);
            }

            // Ensure table is still serving
            $this->db->prepare('UPDATE restaurant_tables SET status = "serving" WHERE id = :id')
                ->execute(['id' => $order['table_id']]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateOrderItemStatus(int $itemId, string $newStatus, ?int $changedBy = null): bool
    {
        if (!array_key_exists($newStatus, self::ITEM_STATUSES)) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT order_id, status FROM order_items WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item === false) {
                $this->db->rollBack();
                return false;
            }

            $orderId = (int) $item['order_id'];

            // Update item status
            $this->db->prepare('UPDATE order_items SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id')
                ->execute(['status' => $newStatus, 'id' => $itemId]);

            // Sync with kitchen ticket items
            $this->db->prepare('UPDATE kitchen_ticket_items SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE order_item_id = :id')
                ->execute(['status' => $newStatus, 'id' => $itemId]);

            // Check if all items are served
            $checkItemsStmt = $this->db->prepare('SELECT status FROM order_items WHERE order_id = :order_id');
            $checkItemsStmt->execute(['order_id' => $orderId]);
            $statuses = $checkItemsStmt->fetchAll(PDO::FETCH_COLUMN);

            $allServed = true;
            foreach ($statuses as $s) {
                if ($s !== 'served' && $s !== 'cancelled') {
                    $allServed = false;
                    break;
                }
            }

            if ($allServed && count($statuses) > 0) {
                $this->updateOrderStatusInternal($orderId, 'served', $changedBy, 'Tất cả các món đã được phục vụ ra bàn');
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function updateOrderStatus(int $orderId, string $newStatus, ?int $changedBy = null, ?string $note = null): bool
    {
        if (!array_key_exists($newStatus, self::ORDER_STATUSES)) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $success = $this->updateOrderStatusInternal($orderId, $newStatus, $changedBy, $note);
            $this->db->commit();
            return $success;
        } catch (Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function updateOrderStatusInternal(int $orderId, string $newStatus, ?int $changedBy = null, ?string $note = null): bool
    {
        $stmt = $this->db->prepare('SELECT status, table_id FROM orders WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order === false) {
            return false;
        }

        $oldStatus = (string) $order['status'];
        if ($oldStatus === $newStatus) {
            return true;
        }

        $upd = $this->db->prepare('UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $upd->execute(['status' => $newStatus, 'id' => $orderId]);

        // Sync table status if order completed or cancelled
        if ($newStatus === 'completed') {
            $this->db->prepare('UPDATE restaurant_tables SET status = "cleaning", updated_at = CURRENT_TIMESTAMP WHERE id = :id')
                ->execute(['id' => $order['table_id']]);
        } elseif ($newStatus === 'cancelled') {
            $this->db->prepare('UPDATE restaurant_tables SET status = "empty", updated_at = CURRENT_TIMESTAMP WHERE id = :id')
                ->execute(['id' => $order['table_id']]);
        }

        // Log history
        $hist = $this->db->prepare(
            'INSERT INTO order_status_histories (order_id, old_status, new_status, changed_by, note)
             VALUES (:order_id, :old_status, :new_status, :changed_by, :note)'
        );
        $hist->execute([
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'note' => $note ?: 'Cập nhật trạng thái sang ' . self::ORDER_STATUSES[$newStatus],
        ]);

        return true;
    }

    public function checkinBooking(int $bookingId, int $staffUserId): int
    {
        $this->db->beginTransaction();
        try {
            $bStmt = $this->db->prepare(
                'SELECT * FROM bookings WHERE id = :id FOR UPDATE'
            );
            $bStmt->execute(['id' => $bookingId]);
            $booking = $bStmt->fetch(PDO::FETCH_ASSOC);

            if ($booking === false) {
                throw new DomainException('Không tìm thấy thông tin đặt bàn.');
            }

            if (!in_array($booking['status'], ['pending', 'confirmed'], true)) {
                throw new DomainException('Lượt đặt bàn này đã được xử lý (trạng thái: ' . $booking['status'] . ').');
            }

            if (empty($booking['table_id'])) {
                throw new DomainException('Lượt đặt bàn này chưa được xếp bàn cụ thể.');
            }

            $tableId = (int) $booking['table_id'];

            // Fetch table
            $tStmt = $this->db->prepare('SELECT * FROM restaurant_tables WHERE id = :id FOR UPDATE');
            $tStmt->execute(['id' => $tableId]);
            $table = $tStmt->fetch(PDO::FETCH_ASSOC);
            if ($table === false) {
                throw new DomainException('Bàn xếp cho khách không tồn tại.');
            }

            // Fetch pre-ordered dishes
            $dStmt = $this->db->prepare(
                'SELECT bd.*, d.name AS dish_name, d.price AS current_price, d.is_available
                 FROM booking_details bd
                 INNER JOIN dishes d ON d.id = bd.dish_id
                 WHERE bd.booking_id = :booking_id'
            );
            $dStmt->execute(['booking_id' => $bookingId]);
            $preorders = $dStmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate total
            $subtotal = 0.0;
            foreach ($preorders as $po) {
                $unitPrice = (float) $po['unit_price'];
                $subtotal += $unitPrice * (int) $po['quantity'];
            }

            $pricing = $this->getPricingRates();
            $serviceCharge = round($subtotal * $pricing['service_charge_rate']);
            $tax = round($subtotal * $pricing['tax_rate']);
            $grandTotal = $subtotal + $serviceCharge + $tax;

            // Create Order
            $orderStmt = $this->db->prepare(
                'INSERT INTO orders (booking_id, table_id, created_by, customer_name, status, payment_status,
                                     total_amount, discount, service_charge, tax, grand_total, notes)
                 VALUES (:booking_id, :table_id, :created_by, :customer_name, "confirmed", "unpaid",
                         :total_amount, 0, :service_charge, :tax, :grand_total, :notes)'
            );
            $orderStmt->execute([
                'booking_id' => $bookingId,
                'table_id' => $tableId,
                'created_by' => $staffUserId,
                'customer_name' => $booking['customer_name'],
                'total_amount' => $subtotal,
                'service_charge' => $serviceCharge,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'notes' => 'Tiếp nhận từ Đặt bàn #' . $bookingId . ($booking['notes'] ? ': ' . $booking['notes'] : ''),
            ]);
            $orderId = (int) $this->db->lastInsertId();

            if ($preorders !== []) {
                $ticketNumber = 'KT-' . date('Ymd') . '-' . sprintf('%04d', $orderId);
                $ticketStmt = $this->db->prepare(
                    'INSERT INTO kitchen_tickets (order_id, ticket_number, status)
                     VALUES (:order_id, :ticket_number, "pending")'
                );
                $ticketStmt->execute([
                    'order_id' => $orderId,
                    'ticket_number' => $ticketNumber,
                ]);
                $ticketId = (int) $this->db->lastInsertId();

                $itemInsertStmt = $this->db->prepare(
                    'INSERT INTO order_items (order_id, dish_id, dish_name, quantity, unit_price, subtotal, note, status)
                     VALUES (:order_id, :dish_id, :dish_name, :quantity, :unit_price, :subtotal, :note, "confirmed")'
                );

                $kItemStmt = $this->db->prepare(
                    'INSERT INTO kitchen_ticket_items (ticket_id, order_item_id, status)
                     VALUES (:ticket_id, :order_item_id, "confirmed")'
                );

                foreach ($preorders as $po) {
                    $itemSubtotal = (float) $po['unit_price'] * (int) $po['quantity'];
                    $itemInsertStmt->execute([
                        'order_id' => $orderId,
                        'dish_id' => $po['dish_id'],
                        'dish_name' => $po['dish_name'],
                        'quantity' => $po['quantity'],
                        'unit_price' => $po['unit_price'],
                        'subtotal' => $itemSubtotal,
                        'note' => $po['note'],
                    ]);
                    $orderItemId = (int) $this->db->lastInsertId();

                    $kItemStmt->execute([
                        'ticket_id' => $ticketId,
                        'order_item_id' => $orderItemId,
                    ]);
                }
            }

            // Update booking status to seated
            $this->db->prepare('UPDATE bookings SET status = "seated", updated_at = CURRENT_TIMESTAMP WHERE id = :id')
                ->execute(['id' => $bookingId]);

            // Log booking status history
            $this->db->prepare(
                'INSERT INTO booking_status_histories (booking_id, old_status, new_status, changed_by, note)
                 VALUES (:booking_id, :old_status, "seated", :changed_by, "Khách đến nhận bàn và tạo đơn phục vụ")'
            )->execute([
                'booking_id' => $bookingId,
                'old_status' => $booking['status'],
                'changed_by' => $staffUserId,
            ]);

            // Update table status to serving
            $this->db->prepare('UPDATE restaurant_tables SET status = "serving", updated_at = CURRENT_TIMESTAMP WHERE id = :id')
                ->execute(['id' => $tableId]);

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function validateAndPrepareItems(array $rawItems): array
    {
        $dishIds = [];
        foreach ($rawItems as $item) {
            $dishId = (int) ($item['dish_id'] ?? 0);
            if ($dishId > 0) {
                $dishIds[] = $dishId;
            }
        }

        if ($dishIds === []) {
            throw new DomainException('Danh sách món không hợp lệ.');
        }

        $placeholders = implode(',', array_fill(0, count($dishIds), '?'));
        $stmt = $this->db->prepare("SELECT id, name, price, is_available FROM dishes WHERE id IN ($placeholders)");
        $stmt->execute($dishIds);
        $dishes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $dishes[(int) $d['id']] = $d;
        }

        $prepared = [];
        foreach ($rawItems as $item) {
            $dishId = (int) ($item['dish_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            $note = isset($item['note']) && is_string($item['note']) ? trim($item['note']) : null;

            if ($qty <= 0) {
                continue;
            }

            if (!isset($dishes[$dishId])) {
                throw new DomainException("Món ăn với mã #$dishId không tồn tại.");
            }

            $dish = $dishes[$dishId];
            if (!$dish['is_available']) {
                throw new DomainException("Món '{$dish['name']}' hiện đã hết hàng, không thể đặt thêm.");
            }

            $unitPrice = (float) $dish['price'];
            $prepared[] = [
                'dish_id' => $dishId,
                'dish_name' => $dish['name'],
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $qty,
                'note' => $note,
            ];
        }

        if ($prepared === []) {
            throw new DomainException('Vui lòng chọn ít nhất một món ăn với số lượng hợp lệ.');
        }

        return $prepared;
    }

    private function recalculateOrderTotals(int $orderId): void
    {
        $stmt = $this->db->prepare(
            'SELECT SUM(subtotal) AS total FROM order_items WHERE order_id = :order_id AND status <> "cancelled"'
        );
        $stmt->execute(['order_id' => $orderId]);
        $subtotal = (float) ($stmt->fetchColumn() ?: 0.0);

        $pricing = $this->getPricingRates();
        $serviceCharge = round($subtotal * $pricing['service_charge_rate']);
        $tax = round($subtotal * $pricing['tax_rate']);
        $grandTotal = $subtotal + $serviceCharge + $tax;

        $upd = $this->db->prepare(
            'UPDATE orders
             SET total_amount = :subtotal,
                 service_charge = :service_charge,
                 tax = :tax,
                 grand_total = :grand_total,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $upd->execute([
            'subtotal' => $subtotal,
            'service_charge' => $serviceCharge,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'id' => $orderId,
        ]);
    }

    private function getPricingRates(): array
    {
        try {
            $settingModel = new RestaurantSetting($this->db);
            return $settingModel->getPricingRates();
        } catch (\Throwable) {
            return [
                'vat_enabled' => true,
                'tax_rate' => self::TAX_RATE,
                'service_charge_enabled' => true,
                'service_charge_rate' => self::SERVICE_CHARGE_RATE,
            ];
        }
    }
}
