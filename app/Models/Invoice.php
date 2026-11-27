<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use PDO;
use Throwable;

final class Invoice extends BaseModel
{
    public const PAYMENT_METHODS = [
        'cash' => 'Tiền mặt',
        'card' => 'Thẻ ngân hàng',
        'transfer' => 'Chuyển khoản trực tiếp',
        'ewallet' => 'Ví điện tử',
    ];

    public const INVOICE_STATUSES = [
        'draft' => 'Bản nháp',
        'issued' => 'Đã phát hành',
        'paid' => 'Đã thanh toán',
        'partially_refunded' => 'Hoàn tiền một phần',
        'refunded' => 'Đã hoàn tiền toàn bộ',
        'cancelled' => 'Đã hủy',
    ];

    public function allInvoices(?string $date = null, ?string $status = null): array
    {
        $filters = [];
        if ($date !== null && $date !== '') {
            $filters['date'] = $date;
        }
        if ($status !== null && $status !== '') {
            $filters['status'] = $status;
        }

        return $this->listInvoices($filters, 1, 1000);
    }

    public function listInvoices(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(i.invoice_number LIKE :search_inv OR i.customer_name LIKE :search_cust OR t.code LIKE :search_tbl)';
            $pattern = '%' . trim((string) $filters['search']) . '%';
            $params['search_inv'] = $pattern;
            $params['search_cust'] = $pattern;
            $params['search_tbl'] = $pattern;
        }

        if (!empty($filters['date'])) {
            $conditions[] = 'DATE(i.issued_at) = :date';
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(i.issued_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(i.issued_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'i.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $conditions[] = 'i.payment_method = :payment_method';
            $params['payment_method'] = $filters['payment_method'];
        }

        $whereClause = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT i.*, o.id AS order_id, o.table_id, t.code AS table_code, t.name AS table_name, a.name AS area_name,
                       (SELECT COALESCE(SUM(amount), 0) FROM invoice_refunds WHERE invoice_id = i.id AND status = 'completed') AS total_refunded
                FROM invoices i
                INNER JOIN orders o ON o.id = i.order_id
                INNER JOIN restaurant_tables t ON t.id = o.table_id
                INNER JOIN dining_areas a ON a.id = t.area_id
                {$whereClause}
                ORDER BY i.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countInvoices(array $filters = []): int
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(i.invoice_number LIKE :search_inv OR i.customer_name LIKE :search_cust OR t.code LIKE :search_tbl)';
            $pattern = '%' . trim((string) $filters['search']) . '%';
            $params['search_inv'] = $pattern;
            $params['search_cust'] = $pattern;
            $params['search_tbl'] = $pattern;
        }

        if (!empty($filters['date'])) {
            $conditions[] = 'DATE(i.issued_at) = :date';
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(i.issued_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(i.issued_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'i.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $conditions[] = 'i.payment_method = :payment_method';
            $params['payment_method'] = $filters['payment_method'];
        }

        $whereClause = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT COUNT(*)
                FROM invoices i
                INNER JOIN orders o ON o.id = i.order_id
                INNER JOIN restaurant_tables t ON t.id = o.table_id
                INNER JOIN dining_areas a ON a.id = t.area_id
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM invoices WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);

        return $inv === false ? null : $inv;
    }

    public function findByOrderId(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM invoices WHERE order_id = :order_id LIMIT 1');
        $stmt->execute(['order_id' => $orderId]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);

        return $inv === false ? null : $inv;
    }

    public function getInvoiceWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, 
                    o.id AS order_id, o.notes AS order_notes, o.created_at AS order_created_at,
                    o.customer_name AS order_customer_name, o.booking_id,
                    b.user_id AS booking_user_id, b.customer_name AS booking_customer_name,
                    COALESCE(b.deposit_amount, 0) AS booking_deposit_amount,
                    b.deposit_received_at AS booking_deposit_received_at,
                    b.customer_email AS booking_customer_email, b.customer_phone AS booking_customer_phone,
                    t.code AS table_code, t.name AS table_name, a.name AS area_name,
                    u.name AS cashier_name, u.phone AS cashier_phone
             FROM invoices i
             INNER JOIN orders o ON o.id = i.order_id
             LEFT JOIN bookings b ON b.id = o.booking_id
             INNER JOIN restaurant_tables t ON t.id = o.table_id
             INNER JOIN dining_areas a ON a.id = t.area_id
             LEFT JOIN payments p ON p.invoice_id = i.id
             LEFT JOIN users u ON u.id = p.received_by
             WHERE i.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invoice === false) {
            return null;
        }

        // Fetch order items
        $itemStmt = $this->db->prepare(
            'SELECT * FROM order_items WHERE order_id = :order_id AND status <> "cancelled" ORDER BY id ASC'
        );
        $itemStmt->execute(['order_id' => $invoice['order_id']]);
        $invoice['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch payments
        $pStmt = $this->db->prepare(
            'SELECT p.*, u.name AS receiver_name
             FROM payments p
             LEFT JOIN users u ON u.id = p.received_by
             WHERE p.invoice_id = :invoice_id
             ORDER BY p.id ASC'
        );
        $pStmt->execute(['invoice_id' => $id]);
        $invoice['payments'] = $pStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch refunds
        $rfStmt = $this->db->prepare(
            'SELECT r.*, u.name AS processor_name
             FROM invoice_refunds r
             LEFT JOIN users u ON u.id = r.processed_by
             WHERE r.invoice_id = :invoice_id
             ORDER BY r.id DESC'
        );
        $rfStmt->execute(['invoice_id' => $id]);
        $invoice['refunds'] = $rfStmt->fetchAll(PDO::FETCH_ASSOC);

        $totalRefunded = 0.0;
        foreach ($invoice['refunds'] as $rf) {
            if ($rf['status'] === 'completed') {
                $totalRefunded += (float) $rf['amount'];
            }
        }
        $invoice['total_refunded'] = $totalRefunded;
        $invoice['remaining_refundable'] = max(0.0, (float) $invoice['total'] - $totalRefunded);

        // Fetch edit histories
        $ehStmt = $this->db->prepare(
            'SELECT eh.*, u.name AS editor_name
             FROM invoice_edit_histories eh
             LEFT JOIN users u ON u.id = eh.edited_by
             WHERE eh.invoice_id = :invoice_id
             ORDER BY eh.id DESC'
        );
        $ehStmt->execute(['invoice_id' => $id]);
        $invoice['edit_histories'] = $ehStmt->fetchAll(PDO::FETCH_ASSOC);

        return $invoice;
    }

    public function generatePrebillData(int $orderId): ?array
    {
        $orderModel = new Order($this->db);
        $order = $orderModel->getOrderDetails($orderId);
        if ($order === null) {
            return null;
        }

        $items = array_filter(
            $order['items'],
            static fn (array $item): bool => $item['status'] !== 'cancelled'
        );

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) $item['subtotal'];
        }

        $voucher = $this->bookingVoucherSnapshot(isset($order['booking_id']) ? (int) $order['booking_id'] : null);
        $discount = Voucher::calculateDiscount($voucher, $subtotal);
        $tableFee = (float) ($order['table_fee'] ?? 0);
        $setupFee = (float) ($order['setup_fee'] ?? 0);
        $billableSubtotal = max(0.0, $subtotal + $tableFee + $setupFee - $discount);

        // Lấy cấu hình thuế và phí động từ RestaurantSetting
        $settingModel = new RestaurantSetting($this->db);
        $pricing = $settingModel->getPricingRates();

        $serviceCharge = round($billableSubtotal * $pricing['service_charge_rate']);
        $tax = round($billableSubtotal * $pricing['tax_rate']);
        $grandTotal = $billableSubtotal + $serviceCharge + $tax;

        $bookingDepositAmount = 0.0;
        $depositAmount = 0.0;
        $depositReceivedAt = null;
        if (!empty($order['booking_id'])) {
            $depositStmt = $this->db->prepare('SELECT deposit_amount, deposit_received_at FROM bookings WHERE id = :id LIMIT 1');
            $depositStmt->execute(['id' => (int) $order['booking_id']]);
            $deposit = $depositStmt->fetch(PDO::FETCH_ASSOC);
            if ($deposit !== false) {
                $bookingDepositAmount = (float) $deposit['deposit_amount'];
                $depositReceivedAt = $deposit['deposit_received_at'];
                if (!empty($depositReceivedAt)) {
                    $depositAmount = $bookingDepositAmount;
                }
            }
        }

        return [
            'order' => $order,
            'items' => array_values($items),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'voucherCode' => $voucher['code'] ?? null,
            'tableFee' => $tableFee,
            'setupFee' => $setupFee,
            'serviceCharge' => $serviceCharge,
            'tax' => $tax,
            'grandTotal' => $grandTotal,
            'depositAmount' => $depositAmount,
            'bookingDepositAmount' => $bookingDepositAmount,
            'depositReceivedAt' => $depositReceivedAt,
            'amountDue' => max(0.0, $grandTotal - $depositAmount),
            'pricing' => $pricing,
            'prebillNumber' => 'PRE-' . date('Ymd') . '-' . sprintf('%04d', $orderId),
            'generatedAt' => date('Y-m-d H:i:s'),
        ];
    }

    public function confirmPayment(
        int $orderId,
        string $method,
        ?int $receivedBy = null,
        ?string $transactionId = null
    ): int
    {
        if (!array_key_exists($method, self::PAYMENT_METHODS)) {
            throw new DomainException('Phương thức thanh toán không hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            $orderModel = new Order($this->db);
            $order = $orderModel->findById($orderId);

            if ($order === null) {
                throw new DomainException('Không tìm thấy đơn hàng cần thanh toán.');
            }

            if ($order['payment_status'] === 'paid') {
                $existingInv = $this->findByOrderId($orderId);
                if ($existingInv !== null) {
                    $this->db->commit();
                    return (int) $existingInv['id'];
                }
            }

            // Calculate totals from active items
            $itemStmt = $this->db->prepare(
                'SELECT SUM(subtotal) AS subtotal FROM order_items WHERE order_id = :order_id AND status <> "cancelled"'
            );
            $itemStmt->execute(['order_id' => $orderId]);
            $subtotal = (float) ($itemStmt->fetchColumn() ?: 0.0);

            $voucher = $this->bookingVoucherSnapshot(isset($order['booking_id']) ? (int) $order['booking_id'] : null);
            $discount = Voucher::calculateDiscount($voucher, $subtotal);
            $tableFee = (float) ($order['table_fee'] ?? 0);
            $setupFee = (float) ($order['setup_fee'] ?? 0);
            $billableSubtotal = max(0.0, $subtotal + $tableFee + $setupFee - $discount);

            // Dynamic rates from settings
            $settingModel = new RestaurantSetting($this->db);
            $pricing = $settingModel->getPricingRates();

            $serviceCharge = round($billableSubtotal * $pricing['service_charge_rate']);
            $tax = round($billableSubtotal * $pricing['tax_rate']);
            $total = $billableSubtotal + $serviceCharge + $tax;

            $depositAmount = 0.0;
            if (!empty($order['booking_id'])) {
                $depositStmt = $this->db->prepare(
                    'SELECT deposit_amount FROM bookings
                     WHERE id = :id AND deposit_received_at IS NOT NULL LIMIT 1'
                );
                $depositStmt->execute(['id' => (int) $order['booking_id']]);
                $depositAmount = (float) ($depositStmt->fetchColumn() ?: 0.0);
            }
            $amountDue = max(0.0, $total - $depositAmount);

            // Generate unique invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . sprintf('%04d', $orderId);

            // Insert or update invoice
            $existingInv = $this->findByOrderId($orderId);
            if ($existingInv !== null) {
                $invId = (int) $existingInv['id'];
                $updInv = $this->db->prepare(
                    'UPDATE invoices 
                     SET subtotal = :subtotal, discount = :discount, service_charge = :service_charge, tax = :tax,
                         total = :total, payment_method = :method, status = "paid",
                         issued_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id'
                );
                $updInv->execute([
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'service_charge' => $serviceCharge,
                    'tax' => $tax,
                    'total' => $total,
                    'method' => $method,
                    'id' => $invId,
                ]);
            } else {
                $insInv = $this->db->prepare(
                    'INSERT INTO invoices (order_id, invoice_number, customer_name, issued_at, subtotal,
                                           discount, service_charge, tax, total, payment_method, status)
                     VALUES (:order_id, :invoice_number, :customer_name, CURRENT_TIMESTAMP, :subtotal,
                             :discount, :service_charge, :tax, :total, :payment_method, "paid")'
                );
                $insInv->execute([
                    'order_id' => $orderId,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $order['customer_name'] ?: 'Khách hàng',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'service_charge' => $serviceCharge,
                    'tax' => $tax,
                    'total' => $total,
                    'payment_method' => $method,
                ]);
                $invId = (int) $this->db->lastInsertId();
            }

            // Create payment record
            $insPayment = $this->db->prepare(
                'INSERT INTO payments (invoice_id, amount, method, paid_at, received_by, transaction_id)
                 VALUES (:invoice_id, :amount, :method, CURRENT_TIMESTAMP, :received_by, :transaction_id)'
            );
            $insPayment->execute([
                'invoice_id' => $invId,
                'amount' => $amountDue,
                'method' => $method,
                'received_by' => $receivedBy,
                'transaction_id' => $transactionId,
            ]);

            // Update order
            $updOrder = $this->db->prepare(
                'UPDATE orders
                 SET payment_status = "paid",
                     status = "completed",
                     total_amount = :subtotal,
                     discount = :discount,
                     service_charge = :service_charge,
                     tax = :tax,
                     grand_total = :total,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $updOrder->execute([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'service_charge' => $serviceCharge,
                'tax' => $tax,
                'total' => $total,
                'id' => $orderId,
            ]);

            // Log order status history
            $this->db->prepare(
                'INSERT INTO order_status_histories (order_id, old_status, new_status, changed_by, note)
                 VALUES (:order_id, :old_status, "completed", :changed_by, :note)'
            )->execute([
                'order_id' => $orderId,
                'old_status' => $order['status'],
                'changed_by' => $receivedBy,
                'note' => 'Xác nhận thanh toán (' . self::PAYMENT_METHODS[$method] . ') và hoàn tất đơn',
            ]);

            // Update table status to cleaning
            $this->db->prepare(
                'UPDATE restaurant_tables SET status = "cleaning", updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            )->execute(['id' => $order['table_id']]);

            // If there's an associated booking, complete it
            if (!empty($order['booking_id'])) {
                $this->db->prepare(
                    'UPDATE bookings SET status = "completed", updated_at = CURRENT_TIMESTAMP WHERE id = :id'
                )->execute(['id' => $order['booking_id']]);
            }

            ActivityLog::log(
                $this->db,
                $receivedBy,
                'confirm_payment',
                'invoices',
                $invId,
                [
                    'invoice_number' => $invoiceNumber,
                    'total' => $total,
                    'payment_method' => $method,
                ]
            );

            $this->db->commit();

            return $invId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function refund(
        int $invoiceId,
        float $amount,
        string $reason,
        string $method,
        int $processedBy
    ): int {
        if ($amount <= 0) {
            throw new DomainException('Số tiền hoàn trả phải lớn hơn 0.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('Vui lòng nhập lý do hoàn tiền.');
        }

        if (!array_key_exists($method, self::PAYMENT_METHODS)) {
            $method = 'cash';
        }

        $invoice = $this->findById($invoiceId);
        if ($invoice === null) {
            throw new DomainException('Không tìm thấy hóa đơn cần hoàn tiền.');
        }

        if (!in_array($invoice['status'], ['paid', 'partially_refunded'], true)) {
            throw new DomainException('Chỉ có thể hoàn tiền đối với hóa đơn đã thanh toán.');
        }

        // Tính tổng tiền đã hoàn hợp lệ
        $rfStmt = $this->db->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM invoice_refunds WHERE invoice_id = :id AND status = "completed"'
        );
        $rfStmt->execute(['id' => $invoiceId]);
        $alreadyRefunded = (float) $rfStmt->fetchColumn();

        $maxRefundable = (float) $invoice['total'] - $alreadyRefunded;
        if ($amount > $maxRefundable) {
            throw new DomainException(
                'Số tiền yêu cầu hoàn (' . number_format($amount, 0, ',', '.') . ' đ) vượt quá số tiền hợp lệ có thể hoàn (tối đa ' . number_format($maxRefundable, 0, ',', '.') . ' đ).'
            );
        }

        // Kiểm tra chống trùng lặp hoàn tiền trong vòng 30 giây
        $dupStmt = $this->db->prepare(
            'SELECT id FROM invoice_refunds
             WHERE invoice_id = :id AND amount = :amount AND reason = :reason
               AND created_at >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
             LIMIT 1'
        );
        $dupStmt->execute([
            'id' => $invoiceId,
            'amount' => $amount,
            'reason' => $reason,
        ]);
        if ($dupStmt->fetchColumn() !== false) {
            throw new DomainException('Phát hiện yêu cầu hoàn tiền tương tự vừa được gửi. Vui lòng không thao tác lặp lại.');
        }

        $this->db->beginTransaction();
        try {
            // Lưu phiếu hoàn tiền
            $insRf = $this->db->prepare(
                'INSERT INTO invoice_refunds (invoice_id, amount, reason, refund_method, status, processed_by, created_at)
                 VALUES (:invoice_id, :amount, :reason, :refund_method, "completed", :processed_by, CURRENT_TIMESTAMP)'
            );
            $insRf->execute([
                'invoice_id' => $invoiceId,
                'amount' => $amount,
                'reason' => $reason,
                'refund_method' => $method,
                'processed_by' => $processedBy,
            ]);
            $refundId = (int) $this->db->lastInsertId();

            $newTotalRefunded = $alreadyRefunded + $amount;
            $newInvoiceStatus = ($newTotalRefunded >= (float) $invoice['total']) ? 'refunded' : 'partially_refunded';

            $updInv = $this->db->prepare('UPDATE invoices SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $updInv->execute(['status' => $newInvoiceStatus, 'id' => $invoiceId]);

            // Nếu hoàn toàn bộ, cập nhật payment_status của order
            if ($newInvoiceStatus === 'refunded') {
                $updOrd = $this->db->prepare('UPDATE orders SET payment_status = "refunded", updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                $updOrd->execute(['id' => $invoice['order_id']]);
            }

            ActivityLog::log(
                $this->db,
                $processedBy,
                'refund_invoice',
                'invoices',
                $invoiceId,
                [
                    'refund_id' => $refundId,
                    'amount' => $amount,
                    'reason' => $reason,
                    'refund_method' => $method,
                    'invoice_status' => $newInvoiceStatus,
                ]
            );

            $this->db->commit();
            return $refundId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function editInvoice(
        int $invoiceId,
        array $newData,
        string $reason,
        int $editedBy
    ): bool {
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('Bắt buộc phải cung cấp lý do sửa đổi hóa đơn.');
        }

        $invoice = $this->findById($invoiceId);
        if ($invoice === null) {
            throw new DomainException('Không tìm thấy hóa đơn cần sửa đổi.');
        }

        // Không cho phép sửa nếu hóa đơn đã hủy hoặc đã hoàn tiền toàn bộ
        if (in_array($invoice['status'], ['cancelled', 'refunded'], true)) {
            throw new DomainException('Không được phép chỉnh sửa hóa đơn đã hủy hoặc đã hoàn tiền toàn bộ.');
        }

        $oldData = [
            'customer_name' => $invoice['customer_name'],
            'payment_method' => $invoice['payment_method'],
        ];

        $customerName = isset($newData['customer_name']) && is_string($newData['customer_name'])
            ? trim($newData['customer_name'])
            : $invoice['customer_name'];

        $paymentMethod = isset($newData['payment_method']) && is_string($newData['payment_method'])
            ? trim($newData['payment_method'])
            : $invoice['payment_method'];

        if (!array_key_exists($paymentMethod, self::PAYMENT_METHODS)) {
            $paymentMethod = $invoice['payment_method'];
        }

        $cleanNewData = [
            'customer_name' => $customerName,
            'payment_method' => $paymentMethod,
        ];

        // Nếu không có thay đổi
        if ($oldData === $cleanNewData) {
            return true;
        }

        $this->db->beginTransaction();
        try {
            $insHist = $this->db->prepare(
                'INSERT INTO invoice_edit_histories (invoice_id, edited_by, reason, old_data, new_data, created_at)
                 VALUES (:invoice_id, :edited_by, :reason, :old_data, :new_data, CURRENT_TIMESTAMP)'
            );
            $insHist->execute([
                'invoice_id' => $invoiceId,
                'edited_by' => $editedBy,
                'reason' => $reason,
                'old_data' => json_encode($oldData, JSON_UNESCAPED_UNICODE),
                'new_data' => json_encode($cleanNewData, JSON_UNESCAPED_UNICODE),
            ]);

            $upd = $this->db->prepare(
                'UPDATE invoices
                 SET customer_name = :customer_name,
                     payment_method = :payment_method,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $upd->execute([
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
                'id' => $invoiceId,
            ]);

            // Đồng bộ tên khách hàng sang đơn hàng nếu có thay đổi
            if ($customerName !== $invoice['customer_name']) {
                $updOrd = $this->db->prepare('UPDATE orders SET customer_name = :name, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                $updOrd->execute(['name' => $customerName, 'id' => $invoice['order_id']]);
            }

            ActivityLog::log(
                $this->db,
                $editedBy,
                'edit_invoice',
                'invoices',
                $invoiceId,
                [
                    'reason' => $reason,
                    'old' => $oldData,
                    'new' => $cleanNewData,
                ]
            );

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function bookingVoucherSnapshot(?int $bookingId): ?array
    {
        if ($bookingId === null || $bookingId < 1) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT voucher_code AS code, voucher_discount_type AS discount_type,
                    voucher_discount_value AS discount_value,
                    voucher_min_order_amount AS min_order_amount,
                    voucher_max_discount_amount AS max_discount_amount
             FROM bookings WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $bookingId]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

        return $voucher === false || empty($voucher['code']) ? null : $voucher;
    }
}
