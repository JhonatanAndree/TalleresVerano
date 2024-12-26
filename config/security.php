<?php
/**
 * Configuración de seguridad unificada
 * Ruta: Config/security.php
 */

return [
    'waf' => [
        'enabled' => true,
        'rules' => [
            'sql_injection' => true,
            'xss' => true,
            'rfi' => true,
            'path_traversal' => true
        ],
        'ip_whitelist' => ['127.0.0.1'],
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'time_window' => 60
        ]
    ],

    'ssl' => [
        'force_https' => true,
        'hsts_enabled' => true,
        'verify_peer' => true
    ],

    'session' => [
        'lifetime' => 7200,
        'path' => '/',
        'domain' => '.muniporvenir.gob.pe',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
        'encrypt' => true,
        'regenerate_id' => true
    ],

    'csrf' => [
        'token_length' => 32,
        'token_lifetime' => 3600,
        'cookie_name' => 'csrf_token',
        'header_name' => 'X-CSRF-TOKEN'
    ],

    'password' => [
        'min_length' => 8,
        'require_special' => true,
        'require_number' => true,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'hash_algo' => PASSWORD_ARGON2ID,
        'hash_options' => [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]
    ],

    'tokens' => [
        'jwt_secret' => getenv('JWT_SECRET'),
        'jwt_algo' => 'HS256',
        'jwt_lifetime' => 3600,
        'reset_token_length' => 64,
        'reset_token_lifetime' => 1800
    ],

    'encryption' => [
        'method' => 'aes-256-gcm',
        'key' => getenv('ENCRYPTION_KEY'),
        'iv_length' => 12
    ],

    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self' https://cdnjs.cloudflare.com https://*.gob.pe; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https://*.gob.pe;"
    ],

    'cors' => [
        'allowed_origins' => ['https://muniporvenir.gob.pe'],
        'allowed_methods' => ['GET', 'POST'],
        'allowed_headers' => ['X-Requested-With', 'Content-Type', 'Authorization'],
        'expose_headers' => [],
        'max_age' => 3600
    ]
];