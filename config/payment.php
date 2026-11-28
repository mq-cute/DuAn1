<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cổng thanh toán VNPAY (VNPAY Payment Gateway)
    |--------------------------------------------------------------------------
    */
    'vnpay' => [
        'tmn_code' => getenv('VNPAY_TMN_CODE') ?: '',
        'hash_secret' => getenv('VNPAY_HASH_SECRET') ?: '',
        'url' => getenv('VNPAY_URL') ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        'return_url' => getenv('VNPAY_RETURN_URL') ?: ((getenv('APP_URL') ?: 'http://localhost:8000') . '/payment/vnpay/callback'),
        'ipn_url' => getenv('VNPAY_IPN_URL') ?: ((getenv('APP_URL') ?: 'http://localhost:8000') . '/payment/vnpay/ipn'),
        'sandbox' => (getenv('VNPAY_ENV') ?: 'sandbox') !== 'live',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cổng thanh toán VietQR (VietQR Napas 247 Dynamic QR Code)
    |--------------------------------------------------------------------------
    */
    'vietqr' => [
        'bank_id' => getenv('VIETQR_BANK_ID') ?: 'MB',
        'account_no' => getenv('VIETQR_ACCOUNT_NO') ?: '0314892341',
        'account_name' => getenv('VIETQR_ACCOUNT_NAME') ?: 'FOODIES HAUTE CUISINE',
        'template' => 'compact2',
    ],
];
