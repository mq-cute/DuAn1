<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Order;
use App\Services\PaymentGatewayService;
use DomainException;
use Throwable;

final class PaymentController extends Controller
{
    /**
     * Trang Cổng Thanh Toán Trực Tuyến (Checkout)
     */
    public function checkout(string $orderId): void
    {
        $currentUser = $this->requireRole('customer', 'staff', 'admin');
        $id = (int)$orderId;

        $db = Database::connection();
        $orderModel = new Order($db);
        $order = $this->findAuthorizedOrder($orderModel, $id, $currentUser);

        if ($order === null) {
            $this->redirect('/account/orders?error=order_not_found');
        }

        // Kiểm tra quyền: khách sở hữu đơn hoặc nhân viên / admin
        $isCustomer = (($currentUser['role'] ?? '') === 'customer');
        // Nếu đơn đã thanh toán -> chuyển thẳng sang hóa đơn
        if ($order['payment_status'] === 'paid') {
            $invoiceModel = new Invoice($db);
            $invoice = $invoiceModel->findByOrderId($id);
            if ($invoice !== null) {
                $this->redirect('/invoices/' . $invoice['id'] . '?message=already_paid');
            }
        }

        $invoiceModel = new Invoice($db);
        $prebill = $invoiceModel->generatePrebillData($id);
        $totalAmount = (float)($prebill['amountDue'] ?? 0);
        if ($totalAmount <= 0) {
            $this->redirect('/account/orders?error=invalid_order_total');
        }

        $gatewayService = new PaymentGatewayService();
        $vietQr = $gatewayService->generateVietQr($id, $totalAmount, 'FDS ORD ' . $id);

        $layout = $isCustomer ? 'layouts/guest' : 'layouts/staff';

        $this->render('payment/checkout', [
            'pageTitle' => 'Cổng Thanh Toán Trực Tuyến — Đơn #' . $id,
            'currentUser' => $currentUser,
            'order' => $order,
            'totalAmount' => $totalAmount,
            'vietQr' => $vietQr,
            'csrfToken' => Csrf::token(),
            'isCustomer' => $isCustomer,
        ], 200, $layout);
    }

    /**
     * Khởi tạo giao dịch qua Cổng VNPAY
     */
    public function processVnPay(string $orderId): void
    {
        $currentUser = $this->requireRole('customer', 'staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/payment/checkout/' . $orderId . '?error=csrf_expired');
        }

        $id = (int)$orderId;
        $db = Database::connection();
        $orderModel = new Order($db);
        $order = $this->findAuthorizedOrder($orderModel, $id, $currentUser);

        if ($order === null) {
            $this->redirect('/account/orders?error=order_not_found');
        }

        $prebill = (new Invoice($db))->generatePrebillData($id);
        $totalAmount = (float)($prebill['amountDue'] ?? 0);
        if ($totalAmount <= 0 || $order['payment_status'] === 'paid') {
            $this->redirect('/account/orders?error=invalid_order_total');
        }

        $orderInfo = 'Thanh toan don hang FOODIES #' . $id . ' - Ban ' . ($order['table_code'] ?? 'VIP');
        $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        $gatewayService = new PaymentGatewayService();

        try {
            $vnpayUrl = $gatewayService->createVnPayUrl($id, $totalAmount, $orderInfo, $clientIp);
            $this->redirect($vnpayUrl);
        } catch (DomainException $exception) {
            $this->redirect('/payment/checkout/' . $id . '?error=merchant_not_configured');
        }
    }

    /**
     * Tiếp nhận kết quả trả về từ Cổng VNPAY (Return URL Callback)
     */
    public function vnpayCallback(): void
    {
        $currentUser = Auth::user();
        $params = $_GET;

        $gatewayService = new PaymentGatewayService();
        $orderId = 0;
        $amount = 0.0;
        $transactionNo = '';
        $verifyResult = $gatewayService->verifyVnPayCallback($params);
        $orderId = $verifyResult['order_id'];
        $amount = $verifyResult['amount'];
        $transactionNo = $verifyResult['transaction_no'];
        $isSuccess = $verifyResult['is_success'];

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $invoiceId = 0;

        $orderModel = new Order($db);
        $order = $orderId > 0 ? $orderModel->getOrderDetails($orderId) : null;
        $prebill = $order === null ? null : (new Invoice($db))->generatePrebillData($orderId);
        $expectedTotal = (float)($prebill['amountDue'] ?? 0);
        $paymentConfig = require dirname(__DIR__, 2) . '/config/payment.php';
        $responseCode = (string)($params['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string)($params['vnp_TransactionStatus'] ?? '');
        $merchantMatches = $verifyResult['merchant_code'] !== ''
            && hash_equals((string)$paymentConfig['vnpay']['tmn_code'], $verifyResult['merchant_code']);
        $amountMatches = $order !== null && abs($amount - $expectedTotal) < 0.005;
        $isSuccess = $verifyResult['is_valid']
            && $responseCode === '00'
            && $transactionStatus === '00'
            && $order !== null
            && $merchantMatches
            && $amountMatches;

        $this->logVnpayDiagnostic(sprintf(
            'return order=%d response=%s transaction_status=%s signature=%s merchant=%s order_exists=%s amount_matches=%s returned_amount=%.2f expected_amount=%.2f',
            $orderId,
            $responseCode !== '' ? $responseCode : 'missing',
            $transactionStatus !== '' ? $transactionStatus : 'missing',
            $verifyResult['is_valid'] ? 'valid' : 'invalid',
            $merchantMatches ? 'match' : 'mismatch',
            $order !== null ? 'yes' : 'no',
            $amountMatches ? 'yes' : 'no',
            $amount,
            $expectedTotal
        ));

        if ($isSuccess && $orderId > 0) {
            try {
                $userId = $currentUser !== null ? (int)$currentUser['id'] : null;
                if ($order['payment_status'] === 'paid') {
                    $existing = $invoiceModel->findByOrderId($orderId);
                    $invoiceId = (int)($existing['id'] ?? 0);
                } else {
                    $invoiceId = $invoiceModel->confirmPayment($orderId, 'ewallet', $userId, $transactionNo);
                }

                // Tạo thông báo xác nhận thanh toán
                if ($userId !== null && $order['payment_status'] !== 'paid') {
                    $notifModel = new Notification($db);
                    $notifModel->create(
                        $userId,
                        'payment_success',
                        'Thanh toán điện tử thành công',
                        'Đơn hàng #' . $orderId . ' đã được thanh toán thành công qua cổng VNPAY. Mã GD: ' . $transactionNo,
                        [
                            'order_id' => $orderId,
                            'invoice_id' => $invoiceId,
                            'amount' => $amount,
                            'transaction_no' => $transactionNo,
                            'gateway' => 'VNPAY',
                        ]
                    );
                }
            } catch (Throwable $e) {
                $this->logVnpayDiagnostic('invoice persistence failed: ' . get_class($e) . ' ' . $e->getMessage());
                // Chỉ giữ kết quả thành công nếu hóa đơn đã được ghi nhận là đã thanh toán.
                $existing = $invoiceModel->findByOrderId($orderId);
                if ($existing !== null && ($existing['status'] ?? '') === 'paid') {
                    $invoiceId = (int)$existing['id'];
                } else {
                    $isSuccess = false;
                }
            }
        }

        $this->render('payment/result', [
            'pageTitle' => 'Kết Quả Giao Dịch — Cổng Thanh Toán VNPAY',
            'currentUser' => $currentUser,
            'isSuccess' => $isSuccess,
            'orderId' => $orderId,
            'invoiceId' => $invoiceId,
            'amount' => $amount,
            'transactionNo' => $transactionNo,
            'gateway' => 'VNPAY (Cổng thanh toán điện tử)',
            'responseCode' => (string)($params['vnp_ResponseCode'] ?? '99'),
        ], 200, 'layouts/guest');
    }

    private function logVnpayDiagnostic(string $message): void
    {
        $logFile = dirname(__DIR__, 2) . '/storage/logs/app.log';
        error_log('[' . date(DATE_ATOM) . '] VNPAY ' . $message . PHP_EOL, 3, $logFile);
    }

    /**
     * Xác nhận thanh toán Chuyển khoản VietQR
     */
    public function vietQrConfirm(string $orderId): void
    {
        $currentUser = $this->requireRole('customer', 'staff', 'admin');
        if (!$this->verifyCsrf()) {
            $this->redirect('/payment/checkout/' . $orderId . '?error=csrf_expired');
        }

        $id = (int)$orderId;
        $db = Database::connection();
        $orderModel = new Order($db);
        $order = $this->findAuthorizedOrder($orderModel, $id, $currentUser);
        if ($order === null || $order['payment_status'] === 'paid') {
            $this->redirect('/account/orders?error=order_not_found');
        }
        $invoiceModel = new Invoice($db);

        try {
            $userId = (int)$currentUser['id'];
            $invoiceId = $invoiceModel->confirmPayment($id, 'transfer', $userId);

            // Tạo thông báo xác nhận chuyển khoản
            $notifModel = new Notification($db);
            $notifModel->create(
                $userId,
                'payment_success',
                'Xác nhận thanh toán VietQR thành công',
                'Đơn hàng #' . $id . ' đã được ghi nhận thanh toán qua chuyển khoản VietQR 24/7.',
                [
                    'order_id' => $id,
                    'invoice_id' => $invoiceId,
                    'gateway' => 'VietQR Napas 247',
                ]
            );

            $this->redirect('/invoices/' . $invoiceId . '?message=vietqr_paid');
        } catch (DomainException $e) {
            $this->redirect('/payment/checkout/' . $id . '?error=' . urlencode($e->getMessage()));
        } catch (Throwable $e) {
            $this->redirect('/payment/checkout/' . $id . '?error=payment_failed');
        }
    }

    /**
     * VNPAY IPN Webhook Server-to-Server
     */
    public function vnpayIpn(): void
    {
        $params = $_GET;
        $gatewayService = new PaymentGatewayService();
        $verify = $gatewayService->verifyVnPayCallback($params);

        header('Content-Type: application/json; charset=utf-8');

        if (!$verify['is_valid']) {
            echo json_encode(['RspCode' => '97', 'Message' => 'Invalid Signature']);
            exit;
        }

        $orderId = $verify['order_id'];
        $db = Database::connection();
        $orderModel = new Order($db);
        $order = $orderModel->findById($orderId);

        if ($order === null) {
            echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
            exit;
        }

        if ($order['payment_status'] === 'paid') {
            echo json_encode(['RspCode' => '02', 'Message' => 'Order already confirmed']);
            exit;
        }

        $prebill = (new Invoice($db))->generatePrebillData($orderId);
        $paymentConfig = require dirname(__DIR__, 2) . '/config/payment.php';
        $matchesOrder = $prebill !== null
            && abs($verify['amount'] - (float)$prebill['amountDue']) < 0.005
            && $verify['merchant_code'] === (string)$paymentConfig['vnpay']['tmn_code'];
        if (!$matchesOrder) {
            echo json_encode(['RspCode' => '04', 'Message' => 'Invalid Amount']);
            exit;
        }
        if ($verify['is_success'] && $matchesOrder) {
            $invoiceModel = new Invoice($db);
            $invoiceModel->confirmPayment($orderId, 'ewallet', null, $verify['transaction_no']);
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } else {
            echo json_encode(['RspCode' => '00', 'Message' => 'Payment Cancelled or Failed']);
        }
        exit;
    }

    private function findAuthorizedOrder(Order $orders, int $orderId, array $user): ?array
    {
        if (($user['role'] ?? '') === 'customer') {
            return $orders->getCustomerOrderDetails($orderId, (int)$user['id']);
        }

        return $orders->getOrderDetails($orderId);
    }
}
