<?php

declare(strict_types=1);

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core'
    . DIRECTORY_SEPARATOR . 'Router.php';

final class RouteProbe
{
    public function show(string $slug): void
    {
        echo $slug;
    }
}

$router = new App\Core\Router();
$router->get('/menu/{slug}', [RouteProbe::class, 'show']);

ob_start();
$router->dispatch('GET', '/menu/bo-wagyu-a5');
$result = (string) ob_get_clean();
if ($result !== 'bo-wagyu-a5') {
    throw new RuntimeException('The router did not pass a dynamic route parameter.');
}

http_response_code(200);
ob_start();
$router->dispatch('POST', '/menu/bo-wagyu-a5');
$result = (string) ob_get_clean();
if (http_response_code() !== 405 || $result !== 'Method Not Allowed') {
    throw new RuntimeException('The router did not reject an unsupported method.');
}

$routesFactory = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
$bookingRouter = $routesFactory();
$routesProperty = new ReflectionProperty(App\Core\Router::class, 'routes');
$routesProperty->setAccessible(true);
$routes = array_map(
    static fn (array $route): string => $route['method'] . ' ' . $route['path'],
    $routesProperty->getValue($bookingRouter)
);
foreach ([
    'GET /bookings/new',
    'POST /bookings',
    'GET /account/bookings',
    'GET /account/bookings/{id}',
    'POST /account/bookings/{id}/cancel',
    'GET /admin/bookings',
    'POST /admin/bookings/{id}/status',
    'GET /tables',
    'POST /tables/{id}/status',
    'GET /staff/pos',
    'POST /staff/orders',
    'POST /staff/orders/{id}/items',
    'POST /staff/order-items/{id}/status',
    'POST /staff/orders/{id}/status',
    'POST /staff/bookings/{id}/checkin',
    'GET /staff/orders',
    'GET /staff/orders/{id}',
    'GET /staff/orders/{id}/prebill',
    'POST /staff/orders/{id}/pay',
    'GET /staff/orders/{id}/invoice',
    'GET /invoices/{id}',
    'GET /account/orders/{id}/invoice',
    'GET /account/invoices/{id}',
    'GET /account/orders',
    'GET /account/orders/{id}',
    'GET /account/orders/{id}/status',
    'GET /account/notifications',
    'POST /account/notifications/{id}/read',
    'POST /account/notifications/read-all',
    'GET /account/notifications/unread-count',
    'GET /kitchen',
    'GET /kitchen/queue',
    'GET /kitchen/api/queue',
    'POST /kitchen/items/{id}/status',
    'POST /kitchen/tickets/{id}/status',
    'GET /staff/api/ready-items',
    'GET /admin/dashboard',
    'POST /admin/bookings/{id}/open-table',
    'POST /admin/bookings/{id}/change-table',
    'GET /admin/tables',
    'POST /admin/tables',
    'POST /admin/tables/save-positions',
    'POST /admin/tables/{id}/vip',
    'POST /admin/tables/{id}/edit',
    'POST /admin/tables/{id}/delete',
    'POST /admin/dining-areas',
    'POST /admin/dining-areas/{id}/edit',
    'POST /admin/dining-areas/{id}/delete',
    'GET /admin/combos',
    'POST /admin/vouchers',
    'GET /admin/setups',
    'GET /admin/reports',
    'GET /invoices/{id}/pdf',
    'GET /invoices/{id}/download',
    'GET /payment/checkout/{orderId}',
    'POST /payment/process/{orderId}',
    'GET /payment/vnpay/callback',
    'POST /payment/vietqr/confirm/{orderId}',
    'GET /payment/vnpay/ipn',
    'GET /api/notifications/poll',
] as $expectedRoute) {
    if (!in_array($expectedRoute, $routes, true)) {
        throw new RuntimeException('The booking route was not registered: ' . $expectedRoute);
    }
}

echo "Router tests passed.\n";
