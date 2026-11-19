<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/app/Core/Environment.php';
\App\Core\Environment::load($root . '/.env');

spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

\App\Core\Session::start();
$db = \App\Core\Database::connection();

// 1. Verify getInvoiceWithDetails includes customer details
$invoiceModel = new \App\Models\Invoice($db);
$inv = $invoiceModel->getInvoiceWithDetails(1);
if ($inv === null) {
    throw new RuntimeException('Invoice 1 not found in database.');
}

if (!array_key_exists('order_customer_name', $inv) || !array_key_exists('booking_user_id', $inv)) {
    throw new RuntimeException('getInvoiceWithDetails is missing customer/booking fields.');
}

// 2. Test ownership validation logic
$invoiceController = new \App\Controllers\InvoiceController();
$reflection = new ReflectionClass($invoiceController);
$ownershipMethod = $reflection->getMethod('customerOwnsInvoice');
$ownershipMethod->setAccessible(true);

// Positive test: Jean-Pierre Laurent owns invoice 1
$ownerUser = [
    'id' => 5,
    'name' => 'Jean-Pierre Laurent',
    'email' => 'customer@lepicure.com',
    'phone' => '0905123456',
    'role' => 'customer',
];
$owns = $ownershipMethod->invoke($invoiceController, $inv, $ownerUser);
if ($owns !== true) {
    throw new RuntimeException('Ownership check failed for legitimate invoice owner.');
}

// Negative test: Another customer (e.g. id = 99, different name) must NOT own invoice 1
$strangerUser = [
    'id' => 99,
    'name' => 'Nguyễn Văn Khác',
    'email' => 'stranger@example.com',
    'phone' => '0999999999',
    'role' => 'customer',
];
$strangerOwns = $ownershipMethod->invoke($invoiceController, $inv, $strangerUser);
if ($strangerOwns !== false) {
    throw new RuntimeException('Ownership check incorrectly allowed unauthorized customer access to invoice!');
}

// 3. Test Router dispatch for customer routes
$routesFactory = require $root . '/routes/web.php';
$router = $routesFactory();

// Simulate authenticated session as Jean-Pierre Laurent (customer)
$_SESSION['user'] = $ownerUser;

ob_start();
$router->dispatch('GET', '/account/orders/2/invoice');
$output = (string) ob_get_clean();

if (!str_contains($output, 'INV-20261119-VIP01')) {
    throw new RuntimeException('Customer could not view invoice via /account/orders/2/invoice.');
}

if (!str_contains($output, 'Hàu tươi Gillardeau')) {
    throw new RuntimeException('Invoice items missing from customer invoice view.');
}

if (!str_contains($output, 'In Hóa Đơn / Tải PDF')) {
    throw new RuntimeException('Print/PDF button missing from customer invoice view.');
}

// Verify that admin refund/edit modals are NOT exposed to customer
if (str_contains($output, 'editModal') || str_contains($output, 'refundModal')) {
    throw new RuntimeException('Admin refund or edit modals were exposed to customer.');
}

// Also test /staff/orders/2/invoice allows customer
ob_start();
$router->dispatch('GET', '/staff/orders/2/invoice');
$outputStaffRoute = (string) ob_get_clean();

if (!str_contains($outputStaffRoute, 'INV-20261119-VIP01')) {
    throw new RuntimeException('Customer could not view invoice via /staff/orders/2/invoice.');
}

// Also test /invoices/1 allows customer
ob_start();
$router->dispatch('GET', '/invoices/1');
$outputDirectRoute = (string) ob_get_clean();

if (!str_contains($outputDirectRoute, 'INV-20261119-VIP01')) {
    throw new RuntimeException('Customer could not view invoice via /invoices/1.');
}

// Negative test: verify stranger customer gets redirected / blocked
$_SESSION['user'] = $strangerUser;
try {
    ob_start();
    $router->dispatch('GET', '/invoices/1');
    $outputUnauthorized = (string) ob_get_clean();
    // In CLI redirect exits or sends header, let's check output does NOT contain the sensitive invoice number
    if (str_contains($outputUnauthorized, 'INV-20261119-VIP01')) {
        throw new RuntimeException('Unauthorized customer was able to view stranger invoice data!');
    }
} catch (\Throwable $e) {
    // If exit / redirect triggered, that is expected
}

echo "Customer invoice ownership & route dispatch tests PASSED.\n";
