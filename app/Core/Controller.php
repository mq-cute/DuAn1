<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        View::render($template, $data);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path, true, 303);
        exit;
    }

    protected function requireAuthenticated(): array
    {
        $user = Auth::user();
        if ($user === null) {
            $this->redirect('/login');
        }

        return $user;
    }

    protected function verifyCsrf(): bool
    {
        return Csrf::isValid($_POST['_csrf_token'] ?? null);
    }
}
