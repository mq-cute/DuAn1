<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(
        string $template,
        array $data = [],
        string $layout = 'layouts/guest'
    ): void {
        $viewsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views';
        $templatePath = self::resolve($viewsPath, $template);
        $layoutPath = self::resolve($viewsPath, $layout);

        extract($data, EXTR_SKIP);
        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }

    public static function escape(string|int|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function resolve(string $viewsPath, string $name): string
    {
        if (!preg_match('/\A[a-zA-Z0-9_\/-]+\z/', $name)) {
            throw new RuntimeException('Invalid view name.');
        }

        $path = $viewsPath . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $name) . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('View not found.');
        }

        return $path;
    }
}
