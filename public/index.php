<?php

declare(strict_types=1);

use App\Core\Environment;
use App\Core\Session;

$root = dirname(__DIR__);
require $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core'
    . DIRECTORY_SEPARATOR . 'Environment.php';
Environment::load($root . DIRECTORY_SEPARATOR . '.env');

$autoload = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class) use ($root): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

$appConfig = require $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
date_default_timezone_set($appConfig['timezone']);
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
Session::start();

set_exception_handler(static function (Throwable $exception) use ($root, $appConfig): void {
    $logDirectory = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    $logLine = sprintf(
        "[%s] %s in %s:%d\n",
        date(DATE_ATOM),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    );
    if ((!is_dir($logDirectory) && !mkdir($logDirectory, 0775, true) && !is_dir($logDirectory))
        || !error_log($logLine, 3, $logDirectory . DIRECTORY_SEPARATOR . 'app.log')) {
        error_log($logLine);
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    if ($appConfig['debug']) {
        echo '<h1>Lỗi ứng dụng</h1><p>' . htmlspecialchars(
            $exception->getMessage(),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) . '</p>';
        return;
    }

    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Lỗi hệ thống</title>'
        . '<body><h1>Không thể xử lý yêu cầu</h1><p>Vui lòng thử lại sau.</p></body></html>';
});

$routes = require $root . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
$router = $routes();
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
