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
    public function index(): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $db = Database::connection();
        $invoiceModel = new Invoice($db);

        $search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
        $dateFrom = isset($_GET['date_from']) && is_string($_GET['date_from']) ? trim($_GET['date_from']) : '';
        $dateTo = isset($_GET['date_to']) && is_string($_GET['date_to']) ? trim($_GET['date_to']) : '';
        $status = isset($_GET['status']) && is_string($_GET['status']) ? trim($_GET['status']) : '';
        $paymentMethod = isset($_GET['payment_method']) && is_string($_GET['payment_method']) ? trim($_GET['payment_method']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 15;

        $filters = [];
        if ($search !== '') {
            $filters['search'] = $search;
        }
        if ($dateFrom !== '') {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $filters['date_to'] = $dateTo;
        }
        if ($status !== '' && array_key_exists($status, Invoice::INVOICE_STATUSES)) {
            $filters['status'] = $status;
        }
        if ($paymentMethod !== '' && array_key_exists($paymentMethod, Invoice::PAYMENT_METHODS)) {
            $filters['payment_method'] = $paymentMethod;
        }

        $totalCount = $invoiceModel->countInvoices($filters);
        $totalPages = max(1, (int) ceil($totalCount / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $invoices = $invoiceModel->listInvoices($filters, $page, $perPage);

        $this->render('invoice/index', [
            'pageTitle' => 'Quản Lý Hóa Đơn & Thanh Toán — L\'Épicure',
            'currentUser' => $currentUser,
            'invoices' => $invoices,
            'totalCount' => $totalCount,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'paymentMethod' => $paymentMethod,
            'statuses' => Invoice::INVOICE_STATUSES,
            'paymentMethods' => Invoice::PAYMENT_METHODS,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
        ], 200, 'layouts/staff');
    }

    public function show(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin', 'customer');
        $invId = (int) $id;

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $invoice = $invoiceModel->getInvoiceWithDetails($invId);

        if ($invoice === null) {
            if (($currentUser['role'] ?? '') === 'customer') {
                $this->redirect('/account/orders?error=invoice_not_found');
            }
            $this->redirect('/staff/orders?message=invoice_not_found');
        }

        $isCustomer = (($currentUser['role'] ?? '') === 'customer');
        if ($isCustomer && !$this->customerOwnsInvoice($invoice, $currentUser)) {
            $this->redirect('/account/orders?error=unauthorized');
        }

        $layout = $isCustomer ? 'layouts/guest' : 'layouts/staff';

        $this->render('invoice/show', [
            'pageTitle' => 'Hóa Đơn #' . $invoice['invoice_number'] . ' — L\'Épicure',
            'currentUser' => $currentUser,
            'invoice' => $invoice,
            'paymentMethods' => Invoice::PAYMENT_METHODS,
            'invoiceStatuses' => Invoice::INVOICE_STATUSES,
            'csrfToken' => Csrf::token(),
            'message' => is_string($_GET['message'] ?? null) ? $_GET['message'] : '',
            'error' => is_string($_GET['error'] ?? null) ? $_GET['error'] : '',
            'isCustomer' => $isCustomer,
            'isStaffLayout' => !$isCustomer,
        ], 200, $layout);
    }

    public function refund(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $invId = (int) $id;

        if (!$this->verifyCsrf()) {
            $this->redirect('/invoices/' . $invId . '?error=csrf_expired');
        }

        $amount = isset($_POST['amount']) && is_numeric($_POST['amount']) ? (float) $_POST['amount'] : 0.0;
        $reason = isset($_POST['reason']) && is_string($_POST['reason']) ? trim($_POST['reason']) : '';
        $method = isset($_POST['refund_method']) && is_string($_POST['refund_method']) ? trim($_POST['refund_method']) : 'cash';

        $db = Database::connection();
        $invoiceModel = new Invoice($db);

        try {
            $invoiceModel->refund($invId, $amount, $reason, $method, (int) $currentUser['id']);
            $this->redirect('/invoices/' . $invId . '?message=refund_success');
        } catch (DomainException $e) {
            $this->redirect('/invoices/' . $invId . '?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/invoices/' . $invId . '?error=refund_failed');
        }
    }

    public function edit(string $id): void
    {
        $currentUser = $this->requireRole('staff', 'admin');
        $invId = (int) $id;

        if (!$this->verifyCsrf()) {
            $this->redirect('/invoices/' . $invId . '?error=csrf_expired');
        }

        $customerName = isset($_POST['customer_name']) && is_string($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
        $paymentMethod = isset($_POST['payment_method']) && is_string($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
        $reason = isset($_POST['reason']) && is_string($_POST['reason']) ? trim($_POST['reason']) : '';

        $db = Database::connection();
        $invoiceModel = new Invoice($db);

        try {
            $invoiceModel->editInvoice($invId, [
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
            ], $reason, (int) $currentUser['id']);

            $this->redirect('/invoices/' . $invId . '?message=edit_success');
        } catch (DomainException $e) {
            $this->redirect('/invoices/' . $invId . '?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/invoices/' . $invId . '?error=edit_failed');
        }
    }

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
        $currentUser = $this->requireRole('staff', 'admin', 'customer');
        $id = (int) $orderId;

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $invoice = $invoiceModel->findByOrderId($id);

        if ($invoice === null) {
            if (($currentUser['role'] ?? '') === 'customer') {
                $this->redirect('/account/orders/' . $id . '?error=invoice_not_ready');
            }
            $this->redirect('/staff/orders/' . $id . '/prebill');
        }

        $this->show((string) $invoice['id']);
    }

    private function customerOwnsInvoice(array $invoice, array $currentUser): bool
    {
        $userId = (int) ($currentUser['id'] ?? 0);
        $bookingUserId = isset($invoice['booking_user_id']) && $invoice['booking_user_id'] !== null ? (int) $invoice['booking_user_id'] : 0;
        if ($userId > 0 && $bookingUserId > 0 && $userId === $bookingUserId) {
            return true;
        }

        $userName = mb_strtolower(trim((string) ($currentUser['name'] ?? '')));
        $invCustName = mb_strtolower(trim((string) ($invoice['customer_name'] ?? '')));
        $orderCustName = mb_strtolower(trim((string) ($invoice['order_customer_name'] ?? '')));
        $bookCustName = mb_strtolower(trim((string) ($invoice['booking_customer_name'] ?? '')));

        if ($userName !== '') {
            if ($invCustName !== '' && $userName === $invCustName) {
                return true;
            }
            if ($orderCustName !== '' && $userName === $orderCustName) {
                return true;
            }
            if ($bookCustName !== '' && $userName === $bookCustName) {
                return true;
            }
        }

        $userEmail = strtolower(trim((string) ($currentUser['email'] ?? '')));
        $bookEmail = strtolower(trim((string) ($invoice['booking_customer_email'] ?? '')));
        if ($userEmail !== '' && $bookEmail !== '' && $userEmail === $bookEmail) {
            return true;
        }

        $userPhone = preg_replace('/[^\d]/', '', (string) ($currentUser['phone'] ?? ''));
        $bookPhone = preg_replace('/[^\d]/', '', (string) ($invoice['booking_customer_phone'] ?? ''));
        if ($userPhone !== '' && $bookPhone !== '' && $userPhone === $bookPhone) {
            return true;
        }

        return false;
    }
}
