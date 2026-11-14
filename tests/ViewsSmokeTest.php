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
        require $file;
    }
});

App\Core\Session::start();
$token = App\Core\Csrf::token();
$pages = [
    ['home/index', ['pageTitle' => 'Home', 'currentUser' => null, 'csrfToken' => $token]],
    ['auth/login', [
        'pageTitle' => 'Login', 'currentUser' => null, 'csrfToken' => $token,
        'errors' => [], 'old' => [],
    ]],
    ['auth/register', [
        'pageTitle' => 'Register', 'currentUser' => null, 'csrfToken' => $token,
        'errors' => [], 'old' => [],
    ]],
    ['menu/index', [
        'pageTitle' => 'Menu', 'currentUser' => null,
        'categories' => [['id' => 1, 'name' => 'Món chính', 'slug' => 'mon-chinh', 'dish_count' => 1]],
        'selectedCategory' => null,
        'dishes' => [[
            'id' => 1, 'name' => 'Bò <script>alert(1)</script>', 'slug' => 'bo-wagyu',
            'category_name' => 'Món chính', 'category_slug' => 'mon-chinh',
            'price' => '2850000.00', 'description' => 'Món đặc biệt',
            'image' => null, 'is_available' => 1,
        ]],
        'search' => '', 'sort' => 'featured', 'csrfToken' => $token, 'error' => '',
    ]],
    ['menu/show', [
        'pageTitle' => 'Dish', 'currentUser' => null, 'csrfToken' => $token, 'error' => '',
        'dish' => [
            'id' => 1, 'name' => 'Bò Wagyu', 'slug' => 'bo-wagyu',
            'category_name' => 'Món chính', 'category_slug' => 'mon-chinh',
            'price' => '2850000.00', 'description' => 'Món <script>đặc biệt</script>',
            'preparation_time' => 20, 'is_available' => 1, 'image' => null,
        ],
    ]],
    ['menu/cart', [
        'pageTitle' => 'Cart', 'currentUser' => null,
        'items' => [[
            'dish' => [
                'id' => 1, 'name' => 'Bò Wagyu', 'slug' => 'bo-wagyu',
                'category_name' => 'Món chính', 'price' => '2850000.00', 'image' => null,
            ],
            'quantity' => 1, 'is_orderable' => true,
        ]],
        'subtotalCents' => 285000000, 'csrfToken' => $token, 'error' => '',
    ]],
    ['booking/new', [
        'pageTitle' => 'Booking', 'currentUser' => [
            'id' => 2, 'name' => 'Nguyễn An', 'email' => 'an@example.com', 'role' => 'customer',
        ],
        'input' => [
            'booking_date' => date('Y-m-d', strtotime('+1 day')), 'booking_time' => '18:30',
            'number_of_guests' => 2, 'table_id' => 4, 'customer_name' => 'Nguyễn An',
            'customer_phone' => '0901234567', 'notes' => '', 'dish_notes' => [1 => 'Ít cay'],
        ],
        'errors' => [], 'tables' => [[
            'id' => 4, 'code' => 'A04', 'name' => 'Bàn 4', 'capacity' => 4,
            'area_name' => 'Tầng trệt',
        ]],
        'items' => [[
            'dish' => ['id' => 1, 'name' => 'Bò Wagyu', 'price' => '2850000.00'],
            'quantity' => 1, 'note' => 'Ít cay', 'is_orderable' => true,
        ]],
        'hasMissingCartItems' => false, 'csrfToken' => $token,
        'today' => date('Y-m-d'),
    ]],
    ['booking/history', [
        'pageTitle' => 'Booking history', 'currentUser' => [
            'id' => 2, 'name' => 'Nguyễn An', 'email' => 'an@example.com', 'role' => 'customer',
        ],
        'bookings' => [[
            'id' => 10, 'booking_date' => date('Y-m-d'), 'booking_time' => '18:30:00',
            'number_of_guests' => 2, 'status' => 'pending', 'table_code' => 'A04',
            'table_name' => 'Bàn 4', 'area_name' => 'Tầng trệt',
        ]],
        'message' => 'created',
    ]],
    ['booking/show', [
        'pageTitle' => 'Booking details', 'currentUser' => [
            'id' => 2, 'name' => 'Nguyễn An', 'email' => 'an@example.com', 'role' => 'customer',
        ],
        'booking' => [
            'id' => 10, 'booking_date' => date('Y-m-d'), 'booking_time' => '18:30:00',
            'number_of_guests' => 2, 'status' => 'pending', 'table_code' => 'A04',
            'table_name' => 'Bàn 4', 'area_name' => 'Tầng trệt', 'customer_name' => 'Nguyễn An',
            'customer_phone' => '0901234567', 'notes' => null,
        ],
        'details' => [[
            'dish_name' => 'Bò Wagyu', 'quantity' => 1, 'unit_price' => '2850000.00',
            'note' => 'Ít cay',
        ]],
        'histories' => [[
            'new_status' => 'pending', 'created_at' => date('Y-m-d H:i:s'),
            'changed_by_name' => 'Nguyễn An', 'note' => null,
        ]],
        'message' => 'created', 'csrfToken' => $token,
    ]],
    ['booking/admin-index', [
        'pageTitle' => 'Manage bookings', 'currentUser' => [
            'id' => 3, 'name' => 'Nhân viên', 'email' => 'staff@example.com', 'role' => 'staff',
        ],
        'bookings' => [[
            'id' => 10, 'booking_date' => date('Y-m-d'), 'booking_time' => '18:30:00',
            'customer_name' => 'Nguyễn An', 'customer_phone' => '0901234567',
            'area_name' => 'Tầng trệt', 'table_code' => 'A04', 'number_of_guests' => 2,
            'notes' => null, 'status' => 'pending',
            'preorders' => [[
                'dish_name' => 'Bò Wagyu', 'quantity' => 1, 'note' => 'Ít cay',
            ]],
        ]],
        'date' => date('Y-m-d'), 'status' => 'pending',
        'statuses' => ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'],
        'csrfToken' => $token, 'message' => '',
    ]],
    ['admin/categories/index', [
        'pageTitle' => 'Categories', 'currentUser' => ['name' => 'Admin', 'role' => 'admin'],
        'categories' => [[
            'id' => 1, 'name' => 'Món chính', 'slug' => 'mon-chinh',
            'is_visible' => 1, 'dish_count' => 1,
        ]],
        'csrfToken' => $token, 'errors' => [], 'old' => [], 'message' => '',
    ]],
    ['admin/categories/edit', [
        'pageTitle' => 'Edit category', 'currentUser' => ['name' => 'Admin', 'role' => 'admin'],
        'category' => ['id' => 1, 'name' => 'Món chính', 'slug' => 'mon-chinh',
            'description' => null, 'sort_order' => 0, 'is_visible' => 1],
        'csrfToken' => $token, 'errors' => [],
    ]],
    ['admin/dishes/index', [
        'pageTitle' => 'Dishes', 'currentUser' => ['name' => 'Admin', 'role' => 'admin'],
        'categories' => [['id' => 1, 'name' => 'Món chính', 'is_visible' => 1]],
        'dishes' => [[
            'id' => 1, 'name' => 'Bò Wagyu', 'slug' => 'bo-wagyu',
            'category_name' => 'Món chính', 'price' => '2850000.00',
            'is_visible' => 1, 'is_available' => 1, 'image' => null,
        ]],
        'search' => '', 'selectedCategoryId' => null,
        'csrfToken' => $token, 'message' => '',
    ]],
    ['admin/dishes/form', [
        'pageTitle' => 'New dish', 'currentUser' => ['name' => 'Admin', 'role' => 'admin'],
        'dish' => null, 'categories' => [['id' => 1, 'name' => 'Món chính', 'is_visible' => 1]],
        'csrfToken' => $token, 'errors' => [], 'old' => [],
    ]],
    ['errors/403', ['pageTitle' => 'Forbidden', 'currentUser' => null]],
    ['errors/404', ['pageTitle' => 'Not found', 'currentUser' => null]],
    ['account/index', [
        'pageTitle' => 'Account', 'currentUser' => [
            'id' => 1, 'name' => 'Guest', 'email' => 'guest@example.com', 'role' => 'customer',
        ],
    ]],
    ['table/index', [
        'pageTitle' => 'Sơ đồ bàn', 'currentUser' => ['id' => 1, 'name' => 'Staff', 'role' => 'staff'],
        'areas' => [['id' => 1, 'name' => 'Phòng VIP', 'description' => 'VIP', 'sort_order' => 1]],
        'tablesByArea' => [1 => [
            'area' => ['id' => 1, 'name' => 'Phòng VIP', 'description' => 'VIP', 'sort_order' => 1],
            'tables' => [[
                'id' => 1, 'code' => 'VIP-01', 'name' => 'Bàn 1', 'capacity' => 4,
                'status' => 'empty', 'active_order' => null,
            ]],
        ]],
        'totalTables' => 1, 'counts' => ['empty' => 1], 'selectedAreaId' => null,
        'csrfToken' => $token, 'message' => '',
    ], 'layouts/staff'],
    ['order/pos', [
        'pageTitle' => 'POS', 'currentUser' => ['id' => 1, 'name' => 'Staff', 'role' => 'staff'],
        'areas' => [['id' => 1, 'name' => 'Phòng VIP']],
        'tables' => [[
            'id' => 1, 'code' => 'VIP-01', 'name' => 'Bàn 1', 'capacity' => 4,
            'status' => 'serving', 'area_name' => 'Phòng VIP',
            'active_order_id' => 1, 'customer_name' => 'Khách VIP',
            'items' => [['id' => 1, 'dish_name' => 'Bò Wagyu', 'status' => 'served']],
            'progress_served' => 1, 'progress_total' => 1, 'minutes_seated' => 15,
        ]],
        'selectedTable' => [
            'id' => 1, 'code' => 'VIP-01', 'name' => 'Bàn 1', 'capacity' => 4,
            'status' => 'serving', 'area_name' => 'Phòng VIP',
        ],
        'selectedTableId' => 1,
        'activeOrder' => [
            'id' => 1, 'customer_name' => 'Khách VIP', 'table_id' => 1,
            'total_amount' => 1000000, 'service_charge' => 50000, 'tax' => 80000,
            'grand_total' => 1130000, 'notes' => 'Ghi chú bàn',
            'items' => [[
                'id' => 1, 'dish_name' => 'Bò Wagyu', 'quantity' => 1,
                'unit_price' => 1000000, 'subtotal' => 1000000,
                'status' => 'served', 'note' => '',
            ]],
        ],
        'allDishes' => [['id' => 1, 'name' => 'Bò Wagyu', 'price' => 1000000]],
        'emptyTables' => [], 'selectedAreaId' => null, 'statusFilter' => null,
        'csrfToken' => $token, 'message' => '', 'error' => '',
    ], 'layouts/staff'],
    ['order/index', [
        'pageTitle' => 'Orders', 'currentUser' => ['id' => 1, 'name' => 'Staff', 'role' => 'staff'],
        'orders' => [[
            'id' => 1, 'table_id' => 1, 'table_code' => 'VIP-01', 'area_name' => 'Phòng VIP',
            'customer_name' => 'Khách', 'staff_name' => 'Staff',
            'served_count' => 1, 'item_count' => 1,
            'grand_total' => 1130000, 'payment_status' => 'paid', 'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
        ]],
        'selectedStatus' => null, 'selectedDate' => date('Y-m-d'),
        'orderStatuses' => \App\Models\Order::ORDER_STATUSES,
        'csrfToken' => $token, 'message' => '',
    ], 'layouts/staff'],
    ['order/show', [
        'pageTitle' => 'Order Details', 'currentUser' => ['id' => 1, 'name' => 'Staff', 'role' => 'staff'],
        'order' => [
            'id' => 1, 'table_id' => 1, 'table_code' => 'VIP-01', 'area_name' => 'Phòng VIP',
            'customer_name' => 'Khách', 'total_amount' => 1000000, 'service_charge' => 50000,
            'tax' => 80000, 'grand_total' => 1130000, 'status' => 'completed',
            'payment_status' => 'paid', 'notes' => '', 'created_at' => date('Y-m-d H:i:s'),
            'items' => [[
                'id' => 1, 'dish_name' => 'Bò Wagyu', 'quantity' => 1,
                'unit_price' => 1000000, 'subtotal' => 1000000, 'status' => 'served', 'note' => '',
            ]],
            'histories' => [],
        ],
        'csrfToken' => $token, 'message' => '',
    ], 'layouts/staff'],
    ['invoice/show', [
        'pageTitle' => 'Invoice', 'currentUser' => ['id' => 1, 'name' => 'Staff', 'role' => 'staff'],
        'invoice' => [
            'id' => 1, 'invoice_number' => 'INV-20261114-0001', 'customer_name' => 'Khách',
            'table_code' => 'VIP-01', 'area_name' => 'Phòng VIP', 'cashier_name' => 'Staff',
            'payment_method' => 'card', 'subtotal' => 1000000, 'service_charge' => 50000,
            'tax' => 80000, 'discount' => 0, 'total' => 1130000, 'issued_at' => date('Y-m-d H:i:s'),
            'items' => [[
                'dish_name' => 'Bò Wagyu', 'quantity' => 1, 'unit_price' => 1000000,
                'subtotal' => 1000000, 'note' => '',
            ]],
        ],
        'paymentMethods' => \App\Models\Invoice::PAYMENT_METHODS,
        'csrfToken' => $token, 'message' => '',
    ], 'layouts/staff'],
    ['invoice/prebill', [
        'pageTitle' => 'Prebill', 'currentUser' => ['id' => 1, 'name' => 'Staff', 'role' => 'staff'],
        'prebill' => [
            'order' => [
                'table_id' => 1, 'table_code' => 'VIP-01', 'area_name' => 'Phòng VIP',
                'customer_name' => 'Khách', 'created_at' => date('Y-m-d H:i:s'),
            ],
            'items' => [[
                'dish_name' => 'Bò Wagyu', 'quantity' => 1, 'unit_price' => 1000000,
                'subtotal' => 1000000, 'note' => '',
            ]],
            'subtotal' => 1000000, 'serviceCharge' => 50000, 'tax' => 80000,
            'grandTotal' => 1130000, 'prebillNumber' => 'PRE-001',
            'generatedAt' => date('Y-m-d H:i:s'),
        ],
        'csrfToken' => $token,
    ], 'layouts/staff'],
];

set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

foreach ($pages as $page) {
    $template = $page[0];
    $data = $page[1];
    $layout = $page[2] ?? 'layouts/guest';

    ob_start();
    App\Core\View::render($template, $data, $layout);
    $html = (string) ob_get_clean();
    if ($html === '' || (!str_contains($html, '<!doctype html>') && !str_contains($html, '<!DOCTYPE html>'))) {
        throw new RuntimeException('View failed to render a complete page: ' . $template);
    }
    if (str_contains($html, '<script>alert(1)</script>') || str_contains($html, '<script>đặc biệt</script>')) {
        throw new RuntimeException('View did not escape user-provided text: ' . $template);
    }
}

restore_error_handler();
if (App\Core\View::currency('1234.50') !== '1.234,50 ₫'
    || App\Core\View::currencyFromCents(123450) !== '1.234,50 ₫'
    || App\Core\View::currencyFromCents(123400) !== '1.234 ₫') {
    throw new RuntimeException('Currency formatting did not preserve decimal values.');
}

echo 'View smoke tests passed (' . count($pages) . " pages).\n";
