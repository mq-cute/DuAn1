<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Invoice;
use App\Models\Order;
use DomainException;
use Throwable;

final class InvoiceController extends Controller
{
    public function confirmPayment(string $orderId): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/staff/pos?error=csrf_expired');
        }

        $id = (int) $orderId;
        $method = (string) ($_POST['payment_method'] ?? 'cash');
        $tableId = isset($_POST['table_id']) && is_numeric($_POST['table_id']) ? (int) $_POST['table_id'] : 0;

        $db = Database::connection();
        $invoiceModel = new Invoice($db);

        try {
            $invoiceId = $invoiceModel->confirmPayment($id, $method, (int) $currentUser['id']);
            $this->redirect('/invoices/' . $invoiceId . '?message=payment_confirmed');
        } catch (DomainException $e) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/staff/pos?table_id=' . $tableId . '&error=payment_failed');
        }
    }

    public function prebill(string $orderId): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $id = (int) $orderId;

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $prebill = $invoiceModel->generatePrebillData($id);

        if ($prebill === null) {
            $this->redirect('/staff/pos?error=order_not_found');
        }

        $this->render('invoice/prebill', [
            'pageTitle' => 'Phiếu Tạm Tính — Bàn ' . ($prebill['order']['table_code'] ?? ''),
            'currentUser' => $currentUser,
            'prebill' => $prebill,
            'csrfToken' => Csrf::token(),
        ], 200, 'layouts/staff');
    }

    public function showByOrder(string $orderId): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $id = (int) $orderId;

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $invoice = $invoiceModel->findByOrderId($id);

        if ($invoice === null) {
            // If not yet paid, redirect to prebill
            $this->redirect('/staff/orders/' . $id . '/prebill');
        }

        $this->show((string) $invoice['id']);
    }

    public function show(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin', 'customer');
        $invId = (int) $id;

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $invoice = $invoiceModel->getInvoiceWithDetails($invId);

        if ($invoice === null) {
            $this->redirect('/staff/orders?message=invoice_not_found');
        }

        $this->render('invoice/show', [
            'pageTitle' => 'Hóa Đơn #' . $invoice['invoice_number'] . ' — L\'Épicure',
            'currentUser' => $currentUser,
            'invoice' => $invoice,
            'paymentMethods' => Invoice::PAYMENT_METHODS,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
        ], 200, 'layouts/staff');
    }
}
