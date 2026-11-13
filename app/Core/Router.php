<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) && $path !== '/' ? rtrim($path, '/') : '/';
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if ($route['path'] !== $path) {
                continue;
            }

            $allowedMethods[] = $route['method'];
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            [$controllerClass, $controllerMethod] = $route['action'];
            $controller = new $controllerClass();
            $controller->{$controllerMethod}();
            return;
        }

        if ($allowedMethods !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowedMethods)));
            echo 'Method Not Allowed';
            return;
        }

        http_response_code(404);
        View::render('errors/404', [], 'layouts/guest');
    }

    private function add(string $method, string $path, array $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
        ];
    }
}
