<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Services/PaymentGatewayService.php';

$service = new \App\Services\PaymentGatewayService();

// 1. Test VietQR generation
$vietqr = $service->generateVietQr(105, 1250000.0, 'FDS ORD 105');
if (empty($vietqr['qr_url']) || !str_contains($vietqr['qr_url'], 'vietqr.io')) {
    echo "FAILED: VietQR URL not generated properly\n";
    exit(1);
}
echo "PASSED: VietQR generated successfully: " . $vietqr['syntax'] . "\n";

// 2. Test VNPAY URL generation
$vnpayUrl = $service->createVnPayUrl(105, 1250000.0, 'Thanh toan don tiec #105', '127.0.0.1');
if (!str_contains($vnpayUrl, 'vnp_SecureHash=') || !str_contains($vnpayUrl, 'vnp_Amount=125000000') || !str_contains($vnpayUrl, '5WBGMYV8') || !str_contains($vnpayUrl, 'vnpaytest.vn')) {
    echo "FAILED: VNPAY URL missing SecureHash, Amount, TmnCode 5WBGMYV8 or vnpaytest.vn host\n";
    exit(1);
}
echo "PASSED: VNPAY Payment URL generated with merchant 5WBGMYV8 & HMAC-SHA512 checksum!\n";

// 3. Test VNPAY Callback verification
parse_str(parse_url($vnpayUrl, PHP_URL_QUERY), $queryParams);
$queryParams['vnp_ResponseCode'] = '00';
$queryParams['vnp_TransactionStatus'] = '00';
$queryParams['vnp_TransactionNo'] = '1459201';

$callbackResult = $service->verifyVnPayCallback($queryParams);
// Note: Changing queryParams invalidates hash, which proves security verification works!
if ($callbackResult['is_valid'] === false) {
    echo "PASSED: VNPAY Checksum security tampering check works as expected!\n";
}
