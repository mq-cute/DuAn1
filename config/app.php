<?php

declare(strict_types=1);

return [
    'name' => "L'Épicure Gastronomie",
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Ho_Chi_Minh',
];
