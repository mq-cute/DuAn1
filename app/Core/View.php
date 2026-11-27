<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
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
        try {
            require $templatePath;
            $content = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $exception;
        }

        require $layoutPath;
    }

    public static function escape(mixed $value): string
    {
        if (!is_scalar($value) && $value !== null) {
            $value = '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function currency(mixed $amount): string
    {
        if (!is_string($amount) && !is_int($amount)) {
            throw new InvalidArgumentException('Currency values must be decimal strings or integers.');
        }

        $amount = trim((string) $amount);
        if (preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', $amount, $matches) !== 1) {
            throw new InvalidArgumentException('Currency value is not a valid decimal amount.');
        }

        $whole = number_format((int) $matches[1], 0, ',', '.');
        $fraction = str_pad($matches[2] ?? '0', 2, '0');

        return $whole . ($fraction === '00' ? '' : ',' . $fraction) . ' ₫';
    }

    public static function currencyFromCents(int $cents): string
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Currency cents cannot be negative.');
        }

        $whole = number_format(intdiv($cents, 100), 0, ',', '.');
        $fraction = $cents % 100;

        return $whole . ($fraction === 0 ? '' : ',' . str_pad((string) $fraction, 2, '0', STR_PAD_LEFT)) . ' ₫';
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
