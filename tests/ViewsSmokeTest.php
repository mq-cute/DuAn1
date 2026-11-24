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
    ['invoice/show', [
        'pageTitle' => 'Invoice Customer', 'currentUser' => ['id' => 5, 'name' => 'Jean-Pierre Laurent', 'email' => 'customer@lepicure.com', 'role' => 'customer'],
        'invoice' => [
            'id' => 1, 'order_id' => 2, 'invoice_number' => 'INV-20261119-VIP01', 'customer_name' => 'Jean-Pierre Laurent',
            'table_code' => 'VIP-01', 'area_name' => 'Phòng VIP', 'cashier_name' => 'Staff',
            'payment_method' => 'card', 'subtotal' => 4860000, 'service_charge' => 243000,
            'tax' => 388800, 'discount' => 0, 'total' => 5491800, 'issued_at' => date('Y-m-d H:i:s'),
            'items' => [[
                'dish_name' => 'Hàu tươi Gillardeau', 'quantity' => 2, 'unit_price' => 680000,
                'subtotal' => 1360000, 'note' => '',
            ]],
        ],
        'paymentMethods' => \App\Models\Invoice::PAYMENT_METHODS,
        'csrfToken' => $token, 'message' => '',
    ], 'layouts/guest'],
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
    ['invoice/index', [
        'pageTitle' => 'Invoices', 'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'invoices' => [[
            'id' => 1, 'order_id' => 1, 'invoice_number' => 'INV-001', 'table_code' => 'T01',
            'area_name' => 'VIP', 'customer_name' => 'Khách A', 'issued_at' => date('Y-m-d H:i:s'),
            'total' => 2000000, 'total_refunded' => 0, 'payment_method' => 'card', 'status' => 'paid',
        ]],
        'totalCount' => 1, 'page' => 1, 'totalPages' => 1,
        'search' => '', 'dateFrom' => '', 'dateTo' => '', 'status' => '', 'paymentMethod' => '',
        'statuses' => \App\Models\Invoice::INVOICE_STATUSES,
        'paymentMethods' => \App\Models\Invoice::PAYMENT_METHODS,
        'csrfToken' => $token, 'message' => '', 'error' => '',
    ], 'layouts/staff'],
    ['admin/users/index', [
        'pageTitle' => 'Users', 'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'users' => [[
            'id' => 1, 'name' => 'Admin User', 'email' => 'admin@example.com', 'phone' => '0901234567',
            'avatar' => null, 'role' => 'admin', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'),
        ]],
        'roles' => [['id' => 1, 'name' => 'admin', 'description' => 'Admin']],
        'roleLabels' => \App\Models\User::ROLES,
        'statusLabels' => \App\Models\User::STATUSES,
        'totalUsers' => 1, 'page' => 1, 'totalPages' => 1,
        'search' => '', 'selectedRole' => '', 'selectedStatus' => '',
        'csrfToken' => $token, 'message' => '', 'error' => '',
    ], 'layouts/staff'],
    ['admin/users/logs', [
        'pageTitle' => 'Logs', 'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'logs' => [[
            'id' => 1, 'user_name' => 'Admin', 'user_email' => 'admin@example.com', 'user_role' => 'admin',
            'action' => 'update_user', 'target_type' => 'users', 'target_id' => 2,
            'details' => '{"change": "role"}', 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s'),
        ]],
        'totalLogs' => 1, 'page' => 1, 'totalPages' => 1,
        'action' => '', 'targetType' => '', 'date' => '',
        'csrfToken' => $token,
    ], 'layouts/staff'],
    ['admin/restaurant/index', [
        'pageTitle' => 'Restaurant Settings', 'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'settings' => [
            'restaurant_name' => "L'Épicure", 'restaurant_tagline' => 'Fine dining',
            'restaurant_address' => '18 Le Duan', 'restaurant_phone' => '02838228899',
            'restaurant_email' => 'contact@lepicure.com', 'restaurant_tax_number' => '0314892341',
            'restaurant_description' => 'Fine French dining', 'restaurant_logo' => '',
            'restaurant_cover_image' => '', 'booking_policy' => '2 hours',
            'cancellation_policy' => 'Free before 2h', 'refund_policy' => '100% refund',
            'general_terms' => 'Smart casual',
        ],
        'operatingHours' => [
            ['day_of_week' => 'monday', 'day_name' => 'Thứ Hai', 'open_time' => '10:00:00', 'close_time' => '23:00:00', 'is_closed' => 0],
        ],
        'pricing' => [
            'vat_enabled' => true, 'tax_rate_percent' => 8.0, 'tax_rate' => 0.08,
            'service_charge_enabled' => true, 'service_charge_rate_percent' => 5.0, 'service_charge_rate' => 0.05,
        ],
        'activeTab' => 'general',
        'csrfToken' => $token, 'message' => '', 'error' => '',
    ], 'layouts/staff'],
    ['order/customer-index', [
        'pageTitle' => 'Đơn Của Tôi',
        'currentUser' => ['id' => 5, 'name' => 'Jean-Pierre Laurent', 'email' => 'customer@lepicure.com', 'role' => 'customer'],
        'orders' => [[
            'id' => 1, 'table_id' => 4, 'table_code' => 'T-04', 'table_name' => 'Bàn 4', 'area_name' => 'Sảnh Vòm',
            'status' => 'preparing', 'payment_status' => 'unpaid', 'grand_total' => '7401500.00',
            'item_count' => 4, 'served_count' => 2, 'created_at' => date('Y-m-d H:i:s'), 'booking_id' => 1,
        ]],
        'selectedStatus' => '',
        'orderStatuses' => App\Models\Order::ORDER_STATUSES,
        'message' => '', 'error' => '',
    ], 'layouts/guest'],
    ['order/customer-tracking', [
        'pageTitle' => 'Theo Dõi Đơn Hàng',
        'currentUser' => ['id' => 5, 'name' => 'Jean-Pierre Laurent', 'email' => 'customer@lepicure.com', 'role' => 'customer'],
        'order' => [
            'id' => 1, 'table_code' => 'T-04', 'table_name' => 'Bàn 4', 'area_name' => 'Sảnh Vòm',
            'status' => 'preparing', 'payment_status' => 'unpaid', 'total_amount' => '6550000.00',
            'service_charge' => '327500.00', 'tax' => '524000.00', 'discount' => '0.00', 'grand_total' => '7401500.00',
            'notes' => 'Ít tiêu', 'created_at' => date('Y-m-d H:i:s'),
            'items' => [[
                'id' => 1, 'dish_name' => 'Súp nấm <script>alert(1)</script>', 'dish_image' => null,
                'category_name' => 'Khai vị', 'note' => 'Không tiêu', 'quantity' => 2, 'subtotal' => '900000.00',
                'status' => 'ready',
            ]],
            'histories' => [[
                'id' => 1, 'new_status' => 'preparing', 'note' => 'Bếp đang nấu', 'created_at' => date('Y-m-d H:i:s'),
            ]],
        ],
        'orderStatuses' => App\Models\Order::ORDER_STATUSES,
        'itemStatuses' => App\Models\Order::ITEM_STATUSES,
        'csrfToken' => $token,
    ], 'layouts/guest'],
    ['account/notifications', [
        'pageTitle' => 'Thông Báo Của Tôi',
        'currentUser' => ['id' => 5, 'name' => 'Jean-Pierre Laurent', 'email' => 'customer@lepicure.com', 'role' => 'customer'],
        'notifications' => [[
            'id' => 1, 'type' => 'order_preparing', 'title' => 'Bếp đang nấu <script>alert(1)</script>',
            'message' => 'Món đang chế biến', 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s'),
            'data' => ['order_id' => 1],
        ]],
        'unreadCount' => 1,
        'csrfToken' => $token,
        'message' => '',
    ], 'layouts/guest'],
    ['kitchen/index', [
        'pageTitle' => 'KDS - Màn Hình Bếp',
        'currentUser' => ['id' => 4, 'name' => 'Trần Bếp Trưởng', 'email' => 'kitchen@lepicure.com', 'role' => 'kitchen'],
        'tickets' => [],
        'summary' => [],
        'stats' => [
            'total_active' => 0, 'pending_count' => 0, 'confirmed_count' => 0,
            'preparing_count' => 0, 'ready_count' => 0, 'served_today_count' => 0, 'urgent_count' => 0,
        ],
        'statusFilter' => 'all',
        'viewMode' => 'tickets',
        'csrfToken' => $token,
        'message' => '',
        'error' => '',
    ], 'layouts/staff'],
    ['kitchen/index', [
        'pageTitle' => 'KDS - Màn Hình Bếp',
        'currentUser' => ['id' => 4, 'name' => 'Trần Bếp Trưởng', 'email' => 'kitchen@lepicure.com', 'role' => 'kitchen'],
        'tickets' => [[
            'order_id' => 1,
            'ticket_number' => 'ORD-001',
            'table_code' => 'T-04',
            'table_name' => 'Bàn 04',
            'area_name' => 'Sảnh Chính',
            'customer_name' => 'Jean-Pierre Laurent <script>alert(1)</script>',
            'order_notes' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'elapsed_minutes' => 10,
            'is_urgent' => false,
            'pending_count' => 1,
            'confirmed_count' => 0,
            'preparing_count' => 1,
            'ready_count' => 0,
            'served_count' => 0,
            'counts' => ['pending' => 1, 'confirmed' => 0, 'preparing' => 1, 'ready' => 0, 'served' => 0, 'total' => 2],
            'items' => [
                [
                    'item_id' => 1,
                    'dish_name' => 'Bò Wagyu A5 <script>alert(1)</script>',
                    'quantity' => 1,
                    'note' => 'Tái',
                    'item_status' => 'preparing',
                    'elapsed_minutes' => 10,
                    'is_urgent' => false,
                    'dish_image' => null,
                    'category_name' => 'Món chính',
                ],
                [
                    'item_id' => 2,
                    'dish_name' => 'Súp Nấm Truffle',
                    'quantity' => 2,
                    'note' => '',
                    'item_status' => 'pending',
                    'elapsed_minutes' => 5,
                    'is_urgent' => false,
                    'dish_image' => null,
                    'category_name' => 'Món khai vị',
                ],
            ],
        ]],
        'summary' => [
            [
                'dish_name' => 'Bò Wagyu A5', 'total_qty' => 1,
                'pending_qty' => 0, 'confirmed_qty' => 0, 'preparing_qty' => 1, 'ready_qty' => 0,
                'tables' => ['Bàn 04'],
            ],
            [
                'dish_name' => 'Súp Nấm Truffle', 'total_qty' => 2,
                'pending_qty' => 2, 'confirmed_qty' => 0, 'preparing_qty' => 0, 'ready_qty' => 0,
                'tables' => ['Bàn 04'],
            ],
        ],
        'stats' => [
            'total_active' => 1, 'pending_count' => 1, 'confirmed_count' => 0,
            'preparing_count' => 1, 'ready_count' => 0, 'served_today_count' => 3, 'urgent_count' => 0,
        ],
        'statusFilter' => 'all',
        'viewMode' => 'tickets',
        'csrfToken' => $token,
        'message' => '',
        'error' => '',
    ], 'layouts/staff'],
    ['admin/dashboard', [
        'pageTitle' => 'Tổng quan Vận hành & Doanh thu — FOODIES Admin',
        'currentUser' => ['id' => 1, 'name' => 'Admin <script>alert(1)</script>', 'role' => 'admin'],
        'todayRevenue' => '128450000',
        'todayBookingsCount' => 56,
        'tableStats' => ['active' => 21, 'total' => 24, 'empty' => 3, 'reserved' => 10, 'cleaning' => 1, 'locked' => 0],
        'dishesDoneCount' => 142,
        'topDishes' => [
            ['dish_name' => 'Bò Wagyu A5 <script>alert(1)</script>', 'qty' => 34, 'total' => '83300000'],
        ],
        'upcomingBookings' => [
            [
                'id' => 1, 'customer_name' => 'Vũ Hoàng Quân <script>alert(1)</script>', 'customer_phone' => '0908921888',
                'booking_time' => '19:00:00', 'booking_date' => date('Y-m-d'), 'number_of_guests' => 4,
                'table_code' => 'VIP-02', 'table_name' => 'Phòng VIP', 'is_vip' => 1, 'combo_name' => 'Kỷ niệm ngày cưới',
            ]
        ],
        'floorData' => [
            'tablesByArea' => [
                [
                    'area' => ['name' => 'Sảnh Vòm'],
                    'tables' => [
                        ['code' => 'T-01', 'name' => 'Bàn 01', 'status' => 'empty']
                    ]
                ]
            ]
        ],
        'csrfToken' => $token,
    ], 'layouts/admin'],
    ['admin/tables/index', [
        'pageTitle' => 'Sơ đồ bàn ăn',
        'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'areas' => [['id' => 1, 'name' => 'Sảnh Vòm', 'description' => 'Sảnh chính', 'sort_order' => 1]],
        'tablesByArea' => [
            1 => [
                'area' => ['id' => 1, 'name' => 'Sảnh Vòm', 'description' => 'Sảnh chính', 'sort_order' => 1],
                'tables' => [[
                    'id' => 1, 'code' => 'VIP-01', 'name' => 'Bàn 01', 'capacity' => 4,
                    'status' => 'empty', 'is_vip' => 1, 'table_fee' => 500000,
                    'vip_privileges' => 'Quản gia riêng', 'description' => 'Phòng view đẹp',
                    'position_x' => 100, 'position_y' => 100,
                ]],
            ]
        ],
        'allTables' => [[
            'id' => 1, 'area_id' => 1, 'code' => 'VIP-01', 'name' => 'Bàn 01', 'capacity' => 4,
            'status' => 'empty', 'is_vip' => 1, 'table_fee' => 500000,
            'vip_privileges' => 'Quản gia riêng', 'description' => 'Phòng view đẹp',
            'position_x' => 100, 'position_y' => 100, 'area_name' => 'Sảnh Vòm',
        ]],
        'totalTables' => 1,
        'counts' => ['empty' => 1, 'reserved' => 0, 'in_use' => 0, 'cleaning' => 0, 'locked' => 0],
        'selectedAreaId' => null,
        'setupRequests' => [],
        'csrfToken' => $token,
        'message' => '',
    ], 'layouts/admin'],
    ['admin/promotions/index', [
        'pageTitle' => 'Combos and Vouchers',
        'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'combos' => [[
            'id' => 1, 'name' => 'Combo Tiệc Sinh Nhật', 'price' => 1500000,
            'target_occasion' => 'Sinh Nhật', 'description' => 'Setup trọn gói',
            'includes' => 'Bánh kem & hoa',
        ]],
        'vouchers' => [[
            'id' => 1, 'code' => 'VIP15', 'title' => 'Giảm 15%', 'discount_type' => 'percentage',
            'discount_value' => 15, 'min_order_amount' => 2000000, 'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')), 'description' => 'Ưu đãi',
        ]],
        'csrfToken' => $token,
        'message' => '',
        'error' => '',
    ], 'layouts/admin'],
    ['admin/setups/index', [
        'pageTitle' => 'Setups Management',
        'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'setups' => [[
            'id' => 1, 'table_code' => 'VIP-01', 'table_name' => 'Bàn VIP', 'is_vip' => 1,
            'customer_name' => 'Nguyễn Văn A', 'customer_phone' => '0901234567',
            'booking_date' => date('Y-m-d'), 'booking_time' => '19:00:00', 'number_of_guests' => 4,
            'combo_name' => 'Setup Kỷ Niệm', 'combo_price' => 850000, 'table_fee' => 500000,
            'setup_details' => 'Nến & hoa', 'notes' => 'Yêu cầu lãng mạn',
        ]],
        'csrfToken' => $token,
    ], 'layouts/admin'],
    ['admin/reports/index', [
        'pageTitle' => 'Financial Reports',
        'currentUser' => ['id' => 1, 'name' => 'Admin', 'role' => 'admin'],
        'totalRevenue' => 128450000,
        'dailyRevenues' => [
            ['order_date' => date('Y-m-d'), 'daily_total' => 128450000, 'order_count' => 15],
        ],
        'orderRatio' => [
            'completed_orders' => 14, 'cancelled_orders' => 1, 'total_orders' => 15,
        ],
        'topDishes' => [
            ['dish_name' => 'Thăn Lưng Bò Wagyu A5', 'total_qty' => 34, 'total_sales' => 83300000],
        ],
        'areaStats' => [
            ['area_name' => 'Sảnh Hoàng Gia', 'total_orders' => 10, 'total_revenue' => 90000000],
        ],
        'csrfToken' => $token,
    ], 'layouts/admin'],
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
