<?php

declare(strict_types=1);

$root = dirname(__DIR__);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once $root . '/app/Core/Environment.php';
\App\Core\Environment::load($root . '/.env');
require_once $root . '/app/Core/Database.php';

$db = \App\Core\Database::connection();

echo "Running Staff Operations Tests...\n";

// 1. Test Table Model (Quản lý bàn)
$tableModel = new \App\Models\Table($db);
$areas = $tableModel->allAreas();
if ($areas === []) {
    throw new RuntimeException('Table::allAreas failed or returned empty.');
}
echo "  [PASS] Table::allAreas returned " . count($areas) . " areas.\n";

$tables = $tableModel->allWithAreas();
if ($tables === []) {
    throw new RuntimeException('Table::allWithAreas failed.');
}
echo "  [PASS] Table::allWithAreas returned " . count($tables) . " tables.\n";

$floorData = $tableModel->getFloorPlanData();
if (!isset($floorData['tablesByArea'], $floorData['counts'])) {
    throw new RuntimeException('Table::getFloorPlanData structure invalid.');
}
echo "  [PASS] Table::getFloorPlanData returned valid floor plan structure.\n";

// Test update table status
$firstTable = $tables[0];
$originalStatus = $firstTable['status'];
$tableModel->updateStatus((int) $firstTable['id'], 'cleaning');
$updatedTable = $tableModel->findById((int) $firstTable['id']);
if ($updatedTable['status'] !== 'cleaning') {
    throw new RuntimeException('Table::updateStatus failed to update to cleaning.');
}
$tableModel->updateStatus((int) $firstTable['id'], $originalStatus);
echo "  [PASS] Table::updateStatus verified.\n";

// 2. Test Order Creation (Tạo đơn gọi món tại bàn)
$orderModel = new \App\Models\Order($db);
$dishModel = new \App\Models\Dish($db);
$dishes = $dishModel->allVisible();
if ($dishes === []) {
    throw new RuntimeException('Dishes missing in DB.');
}

// Pick an empty table or set T-01 to empty
$testTable = $tableModel->findByCode('T-01');
if ($testTable === null) {
    throw new RuntimeException('Table T-01 not found.');
}
$tableModel->updateStatus((int) $testTable['id'], 'empty');

// Make sure no active order on T-01
$db->prepare("UPDATE orders SET status = 'cancelled' WHERE table_id = :id AND status NOT IN ('completed', 'cancelled')")
    ->execute(['id' => $testTable['id']]);

$dish1 = $dishes[0];
$dish2 = $dishes[1];
$orderItems = [
    ['dish_id' => (int) $dish1['id'], 'quantity' => 2, 'note' => 'Ít cay'],
    ['dish_id' => (int) $dish2['id'], 'quantity' => 1, 'note' => 'Ăn liền'],
];

$staffUser = $db->query("SELECT id FROM users WHERE email = 'staff@lepicure.com'")->fetch(PDO::FETCH_ASSOC);
$staffId = $staffUser ? (int) $staffUser['id'] : 1;

$orderId = $orderModel->createOrderAtTable(
    (int) $testTable['id'],
    'Khách Hàng Test',
    $staffId,
    $orderItems,
    'Bàn VIP gọi món trực tiếp'
);

if ($orderId <= 0) {
    throw new RuntimeException('Order creation failed.');
}
echo "  [PASS] Order created with ID: $orderId.\n";

// Verify calculations
$createdOrder = $orderModel->getOrderDetails($orderId);
$expectedSubtotal = ((float) $dish1['price'] * 2) + ((float) $dish2['price'] * 1);
$expectedService = round($expectedSubtotal * 0.05);
$expectedTax = round($expectedSubtotal * 0.08);
$expectedGrandTotal = $expectedSubtotal + $expectedService + $expectedTax;

if ((float) $createdOrder['total_amount'] !== $expectedSubtotal) {
    throw new RuntimeException("Subtotal calculation mismatch: {$createdOrder['total_amount']} vs $expectedSubtotal");
}
if ((float) $createdOrder['grand_total'] !== $expectedGrandTotal) {
    throw new RuntimeException("Grand total mismatch: {$createdOrder['grand_total']} vs $expectedGrandTotal");
}
echo "  [PASS] Backend total calculations verified (Subtotal: $expectedSubtotal, Service: $expectedService, Tax: $expectedTax, GrandTotal: $expectedGrandTotal).\n";

// Verify table status is now serving
$checkTable = $tableModel->findById((int) $testTable['id']);
if ($checkTable['status'] !== 'serving') {
    throw new RuntimeException('Table status should be serving after order creation.');
}
echo "  [PASS] Table automatically updated to 'serving'.\n";

// 3. Test Add Items to Order (Gọi thêm món)
if (isset($dishes[2])) {
    $dish3 = $dishes[2];
    $orderModel->addItemsToOrder($orderId, [
        ['dish_id' => (int) $dish3['id'], 'quantity' => 1, 'note' => 'Thêm món tráng miệng'],
    ], $staffId);

    $updatedOrder = $orderModel->getOrderDetails($orderId);
    if (count($updatedOrder['items']) !== 3) {
        throw new RuntimeException('Supplementary items not added properly.');
    }
    echo "  [PASS] Added supplementary dish to order. Total items: " . count($updatedOrder['items']) . ".\n";
}

// 4. Test Update Item Status (Cập nhật trạng thái phục vụ)
$firstItem = $createdOrder['items'][0];
$successServed = $orderModel->updateOrderItemStatus((int) $firstItem['id'], 'served', $staffId);
if (!$successServed) {
    throw new RuntimeException('Failed to update order item status to served.');
}

// Verify item status in DB
$itemCheck = $db->query("SELECT status FROM order_items WHERE id = " . (int) $firstItem['id'])->fetchColumn();
if ($itemCheck !== 'served') {
    throw new RuntimeException("Item status is $itemCheck, expected served.");
}
echo "  [PASS] Item status updated to 'served'.\n";

// 5. Test Pre-Bill Generation (In tạm tính)
$invoiceModel = new \App\Models\Invoice($db);
$prebill = $invoiceModel->generatePrebillData($orderId);
if ($prebill === null || empty($prebill['items']) || (float) $prebill['grandTotal'] <= 0) {
    throw new RuntimeException('Pre-bill generation failed.');
}
echo "  [PASS] Pre-bill generated successfully (Prebill No: {$prebill['prebillNumber']}, Total: {$prebill['grandTotal']}).\n";

// 6. Test Confirm Payment (Xác nhận thanh toán)
$invoiceId = $invoiceModel->confirmPayment($orderId, 'card', $staffId);
if ($invoiceId <= 0) {
    throw new RuntimeException('Payment confirmation failed.');
}

$invoiceDetails = $invoiceModel->getInvoiceWithDetails($invoiceId);
if ($invoiceDetails['status'] !== 'paid') {
    throw new RuntimeException('Invoice status should be paid.');
}
if ($invoiceDetails['payment_method'] !== 'card') {
    throw new RuntimeException('Invoice payment method should be card.');
}

$paidOrder = $orderModel->findById($orderId);
if ($paidOrder['status'] !== 'completed' || $paidOrder['payment_status'] !== 'paid') {
    throw new RuntimeException('Order should be completed and paid.');
}
echo "  [PASS] Payment confirmed & Invoice generated: #{$invoiceDetails['invoice_number']} (Status: paid, Method: card).\n";

// Table should now be in cleaning
$tableAfterPay = $tableModel->findById((int) $testTable['id']);
if ($tableAfterPay['status'] !== 'cleaning') {
    throw new RuntimeException('Table status should transition to cleaning after invoice payment.');
}
echo "  [PASS] Table transitioned to 'cleaning' after payment.\n";

// Clean table back to empty
$tableModel->updateStatus((int) $testTable['id'], 'empty');

// 7. Test Check-in Booking (Tiếp nhận khách & món ăn đặt trước)
$bookingInsert = $db->prepare(
    'INSERT INTO bookings (user_id, customer_name, customer_phone, customer_email, booking_date, booking_time, number_of_guests, area_id, table_id, notes, status)
     VALUES (NULL, "Thực Khách Đặt Trước", "0909999888", "test@booking.com", :bdate, "19:00:00", 4, :area_id, :table_id, "Đặt bàn kỷ niệm", "confirmed")'
);
$bookingInsert->execute([
    'bdate' => date('Y-m-d'),
    'area_id' => $testTable['area_id'],
    'table_id' => $testTable['id'],
]);
$testBookingId = (int) $db->lastInsertId();

// Insert pre-ordered booking detail
$db->prepare(
    'INSERT INTO booking_details (booking_id, dish_id, quantity, unit_price, note)
     VALUES (:bid, :dish_id, 2, :price, "Ưu tiên làm trước")'
)->execute([
    'bid' => $testBookingId,
    'dish_id' => $dish1['id'],
    'price' => $dish1['price'],
]);

$bookingOrderId = $orderModel->checkinBooking($testBookingId, $staffId);
if ($bookingOrderId <= 0) {
    throw new RuntimeException('Checkin booking failed.');
}

$checkBooking = $db->query("SELECT status FROM bookings WHERE id = $testBookingId")->fetchColumn();
if ($checkBooking !== 'seated') {
    throw new RuntimeException("Booking status should be seated, got $checkBooking.");
}

$bookingOrder = $orderModel->getOrderDetails($bookingOrderId);
if (count($bookingOrder['items']) !== 1) {
    throw new RuntimeException('Pre-ordered dish was not converted into order items.');
}
echo "  [PASS] Booking check-in successfully converted pre-ordered dishes into active POS order items.\n";

// Clean up test booking and order
$db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id")->execute(['id' => $bookingOrderId]);
$db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id")->execute(['id' => $testBookingId]);
$tableModel->updateStatus((int) $testTable['id'], 'empty');

echo "\nAll Staff Operations Tests passed successfully!\n";
