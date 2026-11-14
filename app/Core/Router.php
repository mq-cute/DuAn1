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
            $parameters = $this->match($route['path'], $path);
            if ($parameters === null) {
                continue;
            }

            $allowedMethods[] = $route['method'];
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            [$controllerClass, $controllerMethod] = $route['action'];
            $controller = new $controllerClass();
            $controller->{$controllerMethod}(...array_values($parameters));
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

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = '';
        $parameters = [];
        $parts = preg_split('/(\{[a-zA-Z][a-zA-Z0-9_]*\})/', $routePath, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return null;
        }

        foreach ($parts as $part) {
            if (preg_match('/\A\{([a-zA-Z][a-zA-Z0-9_]*)\}\z/', $part, $match) === 1) {
                $parameters[] = $match[1];
                $pattern .= '([^/]+)';
                continue;
            }

            $pattern .= preg_quote($part, '~');
        }

        if (preg_match('~\A' . $pattern . '\z~u', $requestPath, $matches) !== 1) {
            return null;
        }

        $values = array_slice($matches, 1);
        foreach ($values as $index => $value) {
            $values[$index] = rawurldecode($value);
        }

        return array_combine($parameters, $values) ?: [];
    }
}
