<?php

declare(strict_types=1);

namespace App\Services;

use DomainException;

final class PaymentGatewayService
{
    private array $config;

    public function __construct()
    {
        $configFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'payment.php';
        if (!is_file($configFile)) {
            throw new DomainException('Thiếu cấu hình cổng thanh toán.');
        }

        $this->config = require $configFile;
    }

    /**
     * Tạo URL chuyển hướng thanh toán VNPAY chuẩn HMAC-SHA512
     */
    public function createVnPayUrl(int $orderId, float $amount, string $orderInfo, string $clientIp): string
    {
        $vnpay = $this->config['vnpay'] ?? [];
        $tmnCode = trim((string)($vnpay['tmn_code'] ?? ''));
        $hashSecret = trim((string)($vnpay['hash_secret'] ?? ''));
        $vnpUrl = trim((string)($vnpay['url'] ?? ''));
        $returnUrl = trim((string)($vnpay['return_url'] ?? ''));
        if ($tmnCode === '' || $hashSecret === '' || $vnpUrl === '' || $returnUrl === '') {
            throw new DomainException('Chưa cấu hình mã merchant và khóa bí mật VNPAY.');
        }

        $txnRef = (string)$orderId . '_' . time();
        $vnpAmount = (int)round($amount * 100); // VNPAY yêu cầu nhân 100

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (string)$vnpAmount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $clientIp !== '' ? $clientIp : '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $txnRef,
        ];

        ksort($inputData);
        $query = '';
        $hashdata = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashdata .= '&' . urlencode((string)$key) . '=' . urlencode((string)$value);
            } else {
                $hashdata .= urlencode((string)$key) . '=' . urlencode((string)$value);
                $i = 1;
            }
            $query .= urlencode((string)$key) . '=' . urlencode((string)$value) . '&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $hashSecret);
        $vnpUrl = $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;

        return $vnpUrl;
    }

    /**
     * Xác thực kết quả trả về từ VNPAY qua chữ ký HMAC-SHA512
     */
    public function verifyVnPayCallback(array $params): array
    {
        $vnpay = $this->config['vnpay'] ?? [];
        $hashSecret = trim((string)($vnpay['hash_secret'] ?? ''));

        $vnpSecureHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);

        ksort($params);
        $hashData = '';
        $i = 0;
        foreach ($params as $key => $value) {
            if (str_starts_with((string)$key, 'vnp_')) {
                if ($i === 1) {
                    $hashData .= '&' . urlencode((string)$key) . '=' . urlencode((string)$value);
                } else {
                    $hashData .= urlencode((string)$key) . '=' . urlencode((string)$value);
                    $i = 1;
                }
            }
        }

        $secureHash = $hashSecret === '' ? '' : hash_hmac('sha512', $hashData, $hashSecret);
        $isValidSignature = $secureHash !== '' && hash_equals($secureHash, (string)$vnpSecureHash);
        $isSuccess = ($params['vnp_ResponseCode'] ?? '') === '00' && ($params['vnp_TransactionStatus'] ?? '') === '00';

        $txnRef = (string)($params['vnp_TxnRef'] ?? '');
        $orderId = (int)explode('_', $txnRef)[0];
        $amount = (float)($params['vnp_Amount'] ?? 0) / 100;

        return [
            'is_valid' => $isValidSignature,
            'is_success' => $isValidSignature && $isSuccess,
            'order_id' => $orderId,
            'amount' => $amount,
            'transaction_no' => (string)($params['vnp_TransactionNo'] ?? ''),
            'bank_code' => (string)($params['vnp_BankCode'] ?? ''),
            'merchant_code' => (string)($params['vnp_TmnCode'] ?? ''),
            'response_code' => (string)($params['vnp_ResponseCode'] ?? ''),
            'raw_params' => $params,
        ];
    }

    /**
     * Tạo dữ liệu mã VietQR động chuẩn EMVCo Napas 247
     */
    public function generateVietQr(int $orderId, float $amount, string $orderInfo = ''): array
    {
        $vietqr = $this->config['vietqr'] ?? [];
        $bankId = (string)($vietqr['bank_id'] ?? 'MB');
        $accountNo = (string)($vietqr['account_no'] ?? '0314892341');
        $accountName = (string)($vietqr['account_name'] ?? 'LEPICURE GASTRONOMIE FOODIES');
        $template = (string)($vietqr['template'] ?? 'compact2');

        $transferSyntax = $orderInfo !== '' ? $orderInfo : 'FDS ORD ' . $orderId;
        $addInfo = $transferSyntax;

        $qrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png?amount=%d&addInfo=%s&accountName=%s',
            urlencode($bankId),
            urlencode($accountNo),
            urlencode($template),
            (int)round($amount),
            urlencode($addInfo),
            urlencode($accountName)
        );

        return [
            'qr_url' => $qrUrl,
            'bank_id' => $bankId,
            'account_no' => $accountNo,
            'account_name' => $accountName,
            'amount' => $amount,
            'syntax' => $transferSyntax,
        ];
    }
}
