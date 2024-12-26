<?php
/**
 * Configuración de seguridad del sistema
 * @package Config
 */

return [
    // Configuración de sesión
    'session' => [
        'lifetime' => 7200, // 2 horas
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ],

    // Configuración CSRF
    'csrf' => [
        'token_length' => 32,
        'token_lifetime' => 3600, // 1 hora
        'cookie_name' => 'csrf_token',
        'header_name' => 'X-CSRF-TOKEN'
    ],

    // Configuración de contraseñas
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

    // Configuración de tokens
    'tokens' => [
        'jwt_secret' => getenv('JWT_SECRET'),
        'jwt_algo' => 'HS256',
        'jwt_lifetime' => 3600,
        'reset_token_length' => 64,
        'reset_token_lifetime' => 1800 // 30 minutos
    ],

    // Configuración de encriptación
    'encryption' => [
        'method' => 'aes-256-gcm',
        'key' => getenv('ENCRYPTION_KEY'),
        'iv_length' => 12
    ],

    // Headers de seguridad
    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data:; font-src 'self' data: https://cdnjs.cloudflare.com"
    ],

    // Lista de IPs bloqueadas
    'blocked_ips' => [],

    // Límites de intentos
    'rate_limits' => [
        'login' => [
            'attempts' => 5,
            'decay_minutes' => 30
        ],
        'password_reset' => [
            'attempts' => 3,
            'decay_minutes' => 60
        ],
        'api' => [
            'attempts' => 100,
            'decay_minutes' => 1
        ]
    ],

    // Configuración de auditoria
    'audit' => [
        'enabled' => true,
        'log_login_attempts' => true,
        'log_sensitive_data_access' => true,
        'log_changes' => true
    ]
];