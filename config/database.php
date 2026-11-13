<?php

declare(strict_types=1);

$required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
$missing = array_filter(
    $required,
    static fn (string $key): bool => getenv($key) === false
);

if ($missing !== []) {
    throw new RuntimeException(
        'Database configuration is incomplete. Copy .env.example to .env and configure the database.'
    );
}

return [
    'host' => (string) getenv('DB_HOST'),
    'port' => (string) (getenv('DB_PORT') ?: '3306'),
    'database' => (string) getenv('DB_DATABASE'),
    'username' => (string) getenv('DB_USERNAME'),
    'password' => (string) (getenv('DB_PASSWORD') ?: ''),
];
