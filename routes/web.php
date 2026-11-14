<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\BookingAdminController;
use App\Controllers\BookingController;
use App\Controllers\CategoryAdminController;
use App\Controllers\DishAdminController;
use App\Controllers\HomeController;
use App\Controllers\InvoiceController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\TableController;

return static function (): \App\Core\Router {
    $router = new \App\Core\Router();

    $router->get('/', [HomeController::class, 'index']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->get('/account', [HomeController::class, 'account']);
    $router->post('/logout', [AuthController::class, 'logout']);
    $router->get('/menu', [MenuController::class, 'index']);
    $router->get('/menu/{slug}', [MenuController::class, 'show']);
    $router->get('/cart', [MenuController::class, 'cart']);
    $router->post('/cart/items', [MenuController::class, 'addToCart']);
    $router->post('/cart/items/{id}', [MenuController::class, 'updateCart']);
    $router->post('/cart/items/{id}/remove', [MenuController::class, 'removeFromCart']);
    $router->get('/bookings/new', [BookingController::class, 'createForm']);
    $router->post('/bookings', [BookingController::class, 'create']);
    $router->get('/account/bookings', [BookingController::class, 'history']);
    $router->get('/account/bookings/{id}', [BookingController::class, 'show']);
    $router->post('/account/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    $router->get('/admin/bookings', [BookingAdminController::class, 'index']);
    $router->post('/admin/bookings/{id}/status', [BookingAdminController::class, 'updateStatus']);
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

    // Staff POS & Serving Operations (Tạo đơn gọi món, Cập nhật trạng thái phục vụ, Tiếp nhận đặt bàn)
    $router->get('/staff/pos', [OrderController::class, 'pos']);
    $router->post('/staff/orders', [OrderController::class, 'createAtTable']);
    $router->post('/staff/orders/{id}/items', [OrderController::class, 'addItems']);
    $router->post('/staff/order-items/{id}/status', [OrderController::class, 'updateItemStatus']);
    $router->post('/staff/orders/{id}/status', [OrderController::class, 'updateStatus']);
    $router->post('/staff/bookings/{id}/checkin', [OrderController::class, 'checkinBooking']);

    // Staff Orders Management (Quản lý gọi món)
    $router->get('/staff/orders', [OrderController::class, 'index']);
    $router->get('/staff/orders/{id}', [OrderController::class, 'show']);

    // Staff Billing & Invoices (Xác nhận thanh toán, In/Xuất hóa đơn, In tạm tính)
    $router->get('/staff/orders/{id}/prebill', [InvoiceController::class, 'prebill']);
    $router->post('/staff/orders/{id}/pay', [InvoiceController::class, 'confirmPayment']);
    $router->get('/staff/orders/{id}/invoice', [InvoiceController::class, 'showByOrder']);
    $router->get('/invoices/{id}', [InvoiceController::class, 'show']);

    return $router;
};
