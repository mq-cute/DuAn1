<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\AdminDashboardController;
use App\Controllers\BookingAdminController;
use App\Controllers\BookingController;
use App\Controllers\CategoryAdminController;
use App\Controllers\DishAdminController;
use App\Controllers\HomeController;
use App\Controllers\InvoiceController;
use App\Controllers\KitchenController;
use App\Controllers\MenuController;
use App\Controllers\NotificationController;
use App\Controllers\OrderController;
use App\Controllers\PaymentController;
use App\Controllers\PromotionAdminController;
use App\Controllers\RestaurantAdminController;
use App\Controllers\TableAdminController;
use App\Controllers\TableController;
use App\Controllers\UserAdminController;

return static function (): \App\Core\Router {
    $router = new \App\Core\Router();

    $router->get('/', [HomeController::class, 'index']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->post('/logout', [AuthController::class, 'logout']);

    // Tài khoản cá nhân
    $router->get('/account', [AccountController::class, 'profile']);
    $router->post('/account/profile', [AccountController::class, 'updateProfile']);
    $router->post('/account/password', [AccountController::class, 'changePassword']);

    // Thực đơn khách
    $router->get('/menu', [MenuController::class, 'index']);
    $router->get('/menu/{slug}', [MenuController::class, 'show']);
    $router->get('/cart', [MenuController::class, 'cart']);
    $router->post('/cart/items', [MenuController::class, 'addToCart']);
    $router->post('/cart/items/{id}', [MenuController::class, 'updateCart']);
    $router->post('/cart/items/{id}/remove', [MenuController::class, 'removeFromCart']);

    // Đặt bàn khách
    $router->get('/bookings/new', [BookingController::class, 'createForm']);
    $router->post('/bookings', [BookingController::class, 'create']);
    $router->get('/account/bookings', [BookingController::class, 'history']);
    $router->get('/account/bookings/{id}', [BookingController::class, 'show']);
    $router->post('/account/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // CHỨC NĂNG 2: Lịch sử đơn hàng & Theo dõi đơn cho khách
    $router->get('/account/orders', [OrderController::class, 'customerOrders']);
    $router->get('/account/orders/{id}', [OrderController::class, 'customerOrderDetail']);
    $router->get('/account/orders/{id}/status', [OrderController::class, 'customerOrderStatus']);

    // Thông báo cho khách hàng & Quản trị
    $router->get('/account/notifications', [NotificationController::class, 'index']);
    $router->post('/account/notifications/{id}/read', [NotificationController::class, 'markRead']);
    $router->post('/account/notifications/read-all', [NotificationController::class, 'markAllRead']);
    $router->get('/account/notifications/unread-count', [NotificationController::class, 'unreadCountJson']);
    $router->get('/notifications/unread-count', [NotificationController::class, 'unreadCountJson']);
    $router->get('/api/notifications/poll', [NotificationController::class, 'pollJson']);

    // Quản trị trung tâm Admin (Dashboard)
    $router->get('/admin/dashboard', [AdminDashboardController::class, 'index']);

    // Quản lý đặt bàn
    $router->get('/admin/bookings', [BookingAdminController::class, 'index']);
    $router->post('/admin/bookings/{id}/status', [BookingAdminController::class, 'updateStatus']);
    $router->post('/admin/bookings/{id}/open-table', [BookingAdminController::class, 'openTable']);
    $router->post('/admin/bookings/{id}/change-table', [BookingAdminController::class, 'changeTable']);

    // Quản lý bàn & Sơ đồ Admin
    $router->get('/admin/tables', [TableAdminController::class, 'index']);
    $router->post('/admin/tables', [TableAdminController::class, 'createTable']);
    $router->post('/admin/tables/save-positions', [TableAdminController::class, 'savePositions']);
    $router->post('/admin/tables/{id}/status', [TableAdminController::class, 'updateStatus']);
    $router->post('/admin/tables/{id}/vip', [TableAdminController::class, 'updateVip']);
    $router->post('/admin/tables/{id}/edit', [TableAdminController::class, 'updateTable']);
    $router->post('/admin/tables/{id}/delete', [TableAdminController::class, 'deleteTable']);

    // Quản lý khu vực bàn
    $router->post('/admin/dining-areas', [TableAdminController::class, 'createArea']);
    $router->post('/admin/dining-areas/{id}/edit', [TableAdminController::class, 'updateArea']);
    $router->post('/admin/dining-areas/{id}/delete', [TableAdminController::class, 'deleteArea']);

    // Quản lý Combo, Setup & Vouchers
    $router->get('/admin/combos', [PromotionAdminController::class, 'combos']);
    $router->post('/admin/vouchers', [PromotionAdminController::class, 'createVoucher']);
    $router->get('/admin/setups', [PromotionAdminController::class, 'setups']);

    // Báo cáo & Doanh thu Admin
    $router->get('/admin/reports', [AdminDashboardController::class, 'reports']);

    // Quản lý thực đơn
    $router->get('/admin/menu', [DishAdminController::class, 'index']);
    $router->get('/admin/menu/dishes/new', [DishAdminController::class, 'createForm']);
    $router->post('/admin/menu/dishes', [DishAdminController::class, 'create']);
    $router->get('/admin/menu/dishes/{id}/edit', [DishAdminController::class, 'editForm']);
    $router->post('/admin/menu/dishes/{id}', [DishAdminController::class, 'update']);
    $router->post('/admin/menu/dishes/{id}/visibility', [DishAdminController::class, 'toggleVisibility']);
    $router->post('/admin/menu/dishes/{id}/availability', [DishAdminController::class, 'toggleAvailability']);
    $router->post('/admin/menu/dishes/{id}/delete', [DishAdminController::class, 'delete']);
    $router->get('/admin/menu/categories', [CategoryAdminController::class, 'index']);
    $router->post('/admin/menu/categories', [CategoryAdminController::class, 'create']);
    $router->get('/admin/menu/categories/{id}/edit', [CategoryAdminController::class, 'edit']);
    $router->post('/admin/menu/categories/{id}', [CategoryAdminController::class, 'update']);
    $router->post('/admin/menu/categories/{id}/visibility', [CategoryAdminController::class, 'toggle']);
    $router->post('/admin/menu/categories/{id}/delete', [CategoryAdminController::class, 'delete']);

    // Staff Table Management (Quản lý bàn)
    $router->get('/tables', [TableController::class, 'index']);
    $router->post('/tables/{id}/status', [TableController::class, 'updateStatus']);

    // Staff POS & Serving Operations
    $router->get('/staff/pos', [OrderController::class, 'pos']);
    $router->post('/staff/orders', [OrderController::class, 'createAtTable']);
    $router->post('/staff/orders/{id}/items', [OrderController::class, 'addItems']);
    $router->post('/staff/order-items/{id}/status', [OrderController::class, 'updateItemStatus']);
    $router->post('/staff/orders/{id}/status', [OrderController::class, 'updateStatus']);
    $router->post('/staff/bookings/{id}/checkin', [OrderController::class, 'checkinBooking']);

    // Staff Orders Management (Quản lý gọi món)
    $router->get('/staff/orders', [OrderController::class, 'index']);
    $router->get('/staff/orders/{id}', [OrderController::class, 'show']);

    // CHỨC NĂNG 4: Quản lý Luồng Bếp KDS & Hàng đợi thời gian thực
    $router->get('/kitchen', [KitchenController::class, 'index']);
    $router->get('/kitchen/queue', [KitchenController::class, 'index']);
    $router->get('/kitchen/api/queue', [KitchenController::class, 'apiQueue']);
    $router->post('/kitchen/items/{id}/status', [KitchenController::class, 'updateItemStatus']);
    $router->post('/kitchen/tickets/{id}/status', [KitchenController::class, 'updateTicketStatus']);
    $router->get('/staff/api/ready-items', [KitchenController::class, 'apiReadyItems']);

    // CHỨC NĂNG 5: Quản lý Hóa đơn & Cổng Thanh toán Điện tử (VNPAY / VietQR)
    $router->get('/payment/checkout/{orderId}', [PaymentController::class, 'checkout']);
    $router->post('/payment/process/{orderId}', [PaymentController::class, 'processVnPay']);
    $router->get('/payment/vnpay/callback', [PaymentController::class, 'vnpayCallback']);
    $router->post('/payment/vietqr/confirm/{orderId}', [PaymentController::class, 'vietQrConfirm']);
    $router->get('/payment/vnpay/ipn', [PaymentController::class, 'vnpayIpn']);

    $router->get('/account/orders/{id}/invoice', [InvoiceController::class, 'showByOrder']);
    $router->get('/account/invoices/{id}', [InvoiceController::class, 'show']);
    $router->get('/account/invoices/{id}/pdf', [InvoiceController::class, 'pdf']);
    $router->get('/staff/orders/{id}/prebill', [InvoiceController::class, 'prebill']);
    $router->post('/staff/orders/{id}/pay', [InvoiceController::class, 'confirmPayment']);
    $router->get('/staff/orders/{id}/invoice', [InvoiceController::class, 'showByOrder']);
    $router->get('/invoices/{id}', [InvoiceController::class, 'show']);
    $router->get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf']);
    $router->get('/invoices/{id}/download', [InvoiceController::class, 'downloadPdf']);
    $router->get('/admin/invoices', [InvoiceController::class, 'index']);
    $router->post('/admin/invoices/{id}/refund', [InvoiceController::class, 'refund']);
    $router->post('/admin/invoices/{id}/edit', [InvoiceController::class, 'edit']);

    // CHỨC NĂNG 6: Quản lý tài khoản & Phân quyền
    $router->get('/admin/users', [UserAdminController::class, 'index']);
    $router->post('/admin/users', [UserAdminController::class, 'create']);
    $router->post('/admin/users/{id}', [UserAdminController::class, 'update']);
    $router->post('/admin/users/{id}/reset-password', [UserAdminController::class, 'resetPassword']);
    $router->get('/admin/users/logs', [UserAdminController::class, 'logs']);

    // CHỨC NĂNG 7: Quản lý thông tin nhà hàng & Thiết lập
    $router->get('/admin/restaurant', [RestaurantAdminController::class, 'index']);
    $router->post('/admin/restaurant/general', [RestaurantAdminController::class, 'updateGeneral']);
    $router->post('/admin/restaurant/hours', [RestaurantAdminController::class, 'updateHours']);
    $router->post('/admin/restaurant/pricing', [RestaurantAdminController::class, 'updatePricing']);
    $router->post('/admin/restaurant/policies', [RestaurantAdminController::class, 'updatePolicies']);

    return $router;
};
