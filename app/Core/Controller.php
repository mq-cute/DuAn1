<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(
        string $template,
        array $data = [],
        int $status = 200,
        string $layout = 'layouts/guest'
    ): void {
        http_response_code($status);
        View::render($template, $data, $layout);
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

    protected function requireRole(string ...$roles): array
    {
        $user = $this->requireAuthenticated();
        if (!in_array($user['role'] ?? '', $roles, true)) {
            http_response_code(403);
            $layout = match ($user['role'] ?? '') {
                'admin' => 'layouts/admin',
                'staff', 'kitchen' => 'layouts/staff',
                default => 'layouts/guest',
            };
            $this->render('errors/403', [
                'pageTitle' => 'Không có quyền truy cập',
                'currentUser' => $user,
            ], 403, $layout);
            exit;
        }

        return $user;
    }

    protected function verifyCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? null;

        return is_string($token) && Csrf::isValid($token);
    }
}
