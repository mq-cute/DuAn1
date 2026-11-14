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
        'cancelled' => 'Đã hủy',
    ];

    public function allInvoices(?string $date = null, ?string $status = null): array
    {
        $conditions = [];
        $params = [];

        if ($date !== null && $date !== '') {
            $conditions[] = 'DATE(i.issued_at) = :issued_date';
            $params['issued_date'] = $date;
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 'i.status = :status';
            $params['status'] = $status;
        }

        $sql = 'SELECT i.*, o.table_id, t.code AS table_code, t.name AS table_name, a.name AS area_name
                FROM invoices i
                INNER JOIN orders o ON o.id = i.order_id
                INNER JOIN restaurant_tables t ON t.id = o.table_id
                INNER JOIN dining_areas a ON a.id = t.area_id'
            . ($conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '')
            . ' ORDER BY i.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            'SELECT i.*, o.id AS order_id, o.notes AS order_notes, o.created_at AS order_created_at,
                    t.code AS table_code, t.name AS table_name, a.name AS area_name,
                    u.name AS cashier_name, u.phone AS cashier_phone
             FROM invoices i
             INNER JOIN orders o ON o.id = i.order_id
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

        $serviceCharge = round($subtotal * Order::SERVICE_CHARGE_RATE);
        $tax = round($subtotal * Order::TAX_RATE);
        $grandTotal = $subtotal + $serviceCharge + $tax;

        return [
            'order' => $order,
            'items' => array_values($items),
            'subtotal' => $subtotal,
            'serviceCharge' => $serviceCharge,
            'tax' => $tax,
            'grandTotal' => $grandTotal,
            'prebillNumber' => 'PRE-' . date('Ymd') . '-' . sprintf('%04d', $orderId),
            'generatedAt' => date('Y-m-d H:i:s'),
        ];
    }

    public function confirmPayment(int $orderId, string $method, ?int $receivedBy = null): int
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

            $serviceCharge = round($subtotal * Order::SERVICE_CHARGE_RATE);
            $tax = round($subtotal * Order::TAX_RATE);
            $total = $subtotal + $serviceCharge + $tax;

            // Generate unique invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . sprintf('%04d', $orderId);

            // Insert or update invoice
            $existingInv = $this->findByOrderId($orderId);
            if ($existingInv !== null) {
                $invId = (int) $existingInv['id'];
                $updInv = $this->db->prepare(
                    'UPDATE invoices 
                     SET subtotal = :subtotal, service_charge = :service_charge, tax = :tax,
                         total = :total, payment_method = :method, status = "paid",
                         issued_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id'
                );
                $updInv->execute([
                    'subtotal' => $subtotal,
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
                             0, :service_charge, :tax, :total, :payment_method, "paid")'
                );
                $insInv->execute([
                    'order_id' => $orderId,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $order['customer_name'] ?: 'Khách hàng',
                    'subtotal' => $subtotal,
                    'service_charge' => $serviceCharge,
                    'tax' => $tax,
                    'total' => $total,
                    'payment_method' => $method,
                ]);
                $invId = (int) $this->db->lastInsertId();
            }

            // Create payment record
            $insPayment = $this->db->prepare(
                'INSERT INTO payments (invoice_id, amount, method, paid_at, received_by)
                 VALUES (:invoice_id, :amount, :method, CURRENT_TIMESTAMP, :received_by)'
            );
            $insPayment->execute([
                'invoice_id' => $invId,
                'amount' => $total,
                'method' => $method,
                'received_by' => $receivedBy,
            ]);

            // Update order
            $updOrder = $this->db->prepare(
                'UPDATE orders
                 SET payment_status = "paid",
                     status = "completed",
                     total_amount = :subtotal,
                     service_charge = :service_charge,
                     tax = :tax,
                     grand_total = :total,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $updOrder->execute([
                'subtotal' => $subtotal,
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

            $this->db->commit();

            return $invId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
