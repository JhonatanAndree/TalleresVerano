<?php
/**
 * Configuración general de la aplicación
 * Ruta: Config/app.php
 */

return [
    'name' => getenv('APP_NAME', 'Sistema de Talleres'),
    'env' => getenv('APP_ENV', 'production'),
    'debug' => getenv('APP_DEBUG', false),
    'url' => getenv('APP_URL', 'http://localhost'),
    'timezone' => 'America/Lima',
    'locale' => 'es',
    
    'security' => [
        'encryption_key' => getenv('APP_KEY'),
        'cipher' => 'AES-256-CBC',
        'hash_algo' => PASSWORD_ARGON2ID,
        'session_lifetime' => 120, // minutos
        'password_timeout' => 10800 // 3 horas
    ],
    
    'session' => [
        'driver' => 'file',
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => true,
        'files' => storage_path('framework/sessions'),
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'sistema_talleres_session',
        'path' => '/',
        'domain' => null,
        'secure' => true,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    'logging' => [
        'default' => 'stack',
        'channels' => [
            'file' => [
                'driver' => 'single',
                'path' => storage_path('logs/sistema.log'),
                'level' => 'debug',
                'days' => 14,
            ],
            'error' => [
                'driver' => 'single',
                'path' => storage_path('logs/error.log'),
                'level' => 'error',
                'days' => 30,
            ]
        ]
    ],
    
    'maintenance' => [
        'enabled' => false,
        'message' => 'Sistema en mantenimiento. Por favor, inténtelo más tarde.',
        'allowed_ips' => ['127.0.0.1']
    ]
];