<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Services/InvoicePdfService.php';

$mockInvoice = [
    'id' => 999,
    'invoice_number' => 'INV-TEST-2026',
    'customer_name' => 'Nguyễn Văn A (VIP)',
    'issued_at' => '2026-11-26 12:00:00',
    'payment_method' => 'card',
    'status' => 'paid',
    'subtotal' => 2500000,
    'discount' => 200000,
    'service_charge' => 115000,
    'tax' => 193200,
    'total' => 2608200,
    'items' => [
        [
            'dish_name' => 'Bò Wagyu A5 Nướng Than Bơ Tỏi',
            'quantity' => 2,
            'unit_price' => 950000,
            'subtotal' => 1900000,
        ],
        [
            'dish_name' => 'Vang Đỏ Grand Cru Bordeaux',
            'quantity' => 1,
            'unit_price' => 600000,
            'subtotal' => 600000,
        ]
    ]
];

$pdfData = \App\Services\InvoicePdfService::generate($mockInvoice, true);

if (!str_starts_with($pdfData, '%PDF-1.4')) {
    echo "FAILED: PDF does not start with %PDF-1.4 header\n";
    exit(1);
}

if (!str_contains($pdfData, '%%EOF')) {
    echo "FAILED: PDF missing %%EOF trailer\n";
    exit(1);
}

echo "PASSED: InvoicePdfService successfully generated valid PDF (" . strlen($pdfData) . " bytes)!\n";
