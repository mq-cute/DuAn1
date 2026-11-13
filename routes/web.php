<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;

return static function (): \App\Core\Router {
    $router = new \App\Core\Router();

    $router->get('/', [HomeController::class, 'index']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->get('/account', [HomeController::class, 'account']);
    $router->post('/logout', [AuthController::class, 'logout']);

    return $router;
};
