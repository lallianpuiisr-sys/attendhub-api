<?php

$configuredOrigins = env('CORS_ALLOWED_ORIGINS');

$allowedOrigins = [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost:8081',
    'http://127.0.0.1:8081',
    'http://192.168.1.54:8081',
    'http://192.168.1.54:3000',
    'http://192.168.1.54:8000',
];

if (is_string($configuredOrigins) && trim($configuredOrigins) !== '') {
    $allowedOrigins = array_values(array_filter(array_map(
        static fn(string $origin) => trim($origin),
        explode(',', $configuredOrigins)
    )));
}

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];