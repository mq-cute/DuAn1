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
        $currentUser = $this->requireAuthenticated();
        $id = (int)$orderId;

        $db = Database::connection();
        $orderModel = new Order($db);
        $order = $orderModel->getOrderWithDetails($id);

        if ($order === null) {
            $this->redirect('/account/orders?error=order_not_found');
        }

        // Kiểm tra quyền: khách sở hữu đơn hoặc nhân viên / admin
        $isCustomer = (($currentUser['role'] ?? '') === 'customer');
        if ($isCustomer) {
            $orderCustName = mb_strtolower(trim((string)($order['customer_name'] ?? '')));
            $currentName = mb_strtolower(trim((string)($currentUser['name'] ?? '')));
            if ($orderCustName !== '' && $currentName !== '' && $orderCustName !== $currentName) {
                // kiểm tra qua booking
                $bookingUserId = (int)($order['booking_user_id'] ?? 0);
                if ($bookingUserId > 0 && $bookingUserId !== (int)$currentUser['id']) {
                    $this->redirect('/account/orders?error=unauthorized');
                }
            }
        }

        // Nếu đơn đã thanh toán -> chuyển thẳng sang hóa đơn
        if ($order['payment_status'] === 'paid') {
            $invoiceModel = new Invoice($db);
            $invoice = $invoiceModel->findByOrderId($id);
            if ($invoice !== null) {
                $this->redirect('/invoices/' . $invoice['id'] . '?message=already_paid');
            }
        }

        $totalAmount = (float)($order['grand_total'] > 0 ? $order['grand_total'] : $order['total_amount']);
        if ($totalAmount <= 0) {
            // Tính tạm nếu grand_total chưa cập nhật
            $itemSubtotal = 0;
            foreach ($order['items'] ?? [] as $it) {
                if (($it['status'] ?? '') !== 'cancelled') {
                    $itemSubtotal += (float)$it['subtotal'];
                }
            }
            $totalAmount = $itemSubtotal > 0 ? round($itemSubtotal * 1.13) : 500000;
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
        $currentUser = $this->requireAuthenticated();
        if (!$this->verifyCsrf()) {
            $this->redirect('/payment/checkout/' . $orderId . '?error=csrf_expired');
        }

        $id = (int)$orderId;
        $db = Database::connection();
        $orderModel = new Order($db);
        $order = $orderModel->getOrderWithDetails($id);

        if ($order === null) {
            $this->redirect('/account/orders?error=order_not_found');
        }

        $totalAmount = (float)($order['grand_total'] > 0 ? $order['grand_total'] : $order['total_amount']);
        if ($totalAmount <= 0) {
            $totalAmount = 500000;
        }

        $orderInfo = 'Thanh toan don hang FOODIES #' . $id . ' - Ban ' . ($order['table_code'] ?? 'VIP');
        $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        $gatewayService = new PaymentGatewayService();

        // Kiểm tra nếu người dùng chọn chế độ Sandbox Simulation Test
        $simulate = isset($_POST['simulate']) && $_POST['simulate'] === '1';
        if ($simulate) {
            // Chuyển hướng tới URL callback giả lập thành công tức thì cho môi trường Local Dev
            $callbackUrl = '/payment/vnpay/callback?vnp_Amount=' . ((int)round($totalAmount * 100))
                . '&vnp_BankCode=NCB&vnp_BankTranNo=VNP' . time()
                . '&vnp_CardType=ATM&vnp_OrderInfo=' . urlencode($orderInfo)
                . '&vnp_PayDate=' . date('YmdHis')
                . '&vnp_ResponseCode=00&vnp_TmnCode=5WBGMYV8'
                . '&vnp_TransactionNo=' . time()
                . '&vnp_TransactionStatus=00'
                . '&vnp_TxnRef=' . $id . '_' . time()
                . '&is_simulated=1';
            $this->redirect($callbackUrl);
            return;
        }

        $vnpayUrl = $gatewayService->createVnPayUrl($id, $totalAmount, $orderInfo, $clientIp);
        $this->redirect($vnpayUrl);
    }

    /**
     * Tiếp nhận kết quả trả về từ Cổng VNPAY (Return URL Callback)
     */
    public function vnpayCallback(): void
    {
        $currentUser = Auth::user();
        $params = $_GET;

        $gatewayService = new PaymentGatewayService();
        $isSimulated = isset($params['is_simulated']) && $params['is_simulated'] === '1';

        $orderId = 0;
        $amount = 0.0;
        $transactionNo = '';
        $isSuccess = false;

        if ($isSimulated) {
            $txnRef = (string)($params['vnp_TxnRef'] ?? '');
            $orderId = (int)explode('_', $txnRef)[0];
            $amount = (float)($params['vnp_Amount'] ?? 0) / 100;
            $transactionNo = (string)($params['vnp_TransactionNo'] ?? 'SIM-' . time());
            $isSuccess = ($params['vnp_ResponseCode'] ?? '') === '00';
        } else {
            $verifyResult = $gatewayService->verifyVnPayCallback($params);
            $orderId = $verifyResult['order_id'];
            $amount = $verifyResult['amount'];
            $transactionNo = $verifyResult['transaction_no'];
            $isSuccess = $verifyResult['is_success'];
        }

        $db = Database::connection();
        $invoiceModel = new Invoice($db);
        $invoiceId = 0;

        if ($isSuccess && $orderId > 0) {
            try {
                $userId = $currentUser !== null ? (int)$currentUser['id'] : null;
                $invoiceId = $invoiceModel->confirmPayment($orderId, 'ewallet', $userId);

                // Tạo thông báo xác nhận thanh toán
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
            } catch (Throwable $e) {
                // Đã thanh toán hoặc lỗi xử lý
                $existing = $invoiceModel->findByOrderId($orderId);
                if ($existing !== null) {
                    $invoiceId = (int)$existing['id'];
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

    /**
     * Xác nhận thanh toán Chuyển khoản VietQR
     */
    public function vietQrConfirm(string $orderId): void
    {
        $currentUser = $this->requireAuthenticated();
        if (!$this->verifyCsrf()) {
            $this->redirect('/payment/checkout/' . $orderId . '?error=csrf_expired');
        }

        $id = (int)$orderId;
        $db = Database::connection();
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

        if ($verify['is_success']) {
            $invoiceModel = new Invoice($db);
            $invoiceModel->confirmPayment($orderId, 'ewallet', null);
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } else {
            echo json_encode(['RspCode' => '00', 'Message' => 'Payment Cancelled or Failed']);
        }
        exit;
    }
}
