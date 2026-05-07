<?php

function corsAllowedOrigins(): array
{
    $configuredOrigins = env('CORS_ALLOWED_ORIGINS');

    if (is_string($configuredOrigins) && trim($configuredOrigins) !== '') {
        return array_values(array_filter(array_map(
            static fn (string $origin) => trim($origin),
            explode(',', $configuredOrigins)
        )));
    }

    return [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8081',
        'http://127.0.0.1:8081',
        'http://192.168.1.54:8081',
        'http://192.168.1.54:3000',
        'http://192.168.1.54:8000',
    ];
}

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Credentialed requests cannot use a wildcard origin, so we explicitly
    | allow the local frontend origins used during development.
    |
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => corsAllowedOrigins(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
