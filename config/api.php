<?php
/**
 * Configuración de APIs externas
 * Ruta: Config/api.php
 */

return [
    'services' => [
        'yape' => [
            'base_url' => getenv('YAPE_API_URL'),
            'api_key' => getenv('YAPE_API_KEY'),
            'auth_type' => 'bearer',
            'timeout' => 30,
            'retry_attempts' => 3
        ],
        'whatsapp' => [
            'base_url' => getenv('WHATSAPP_API_URL'),
            'username' => getenv('WHATSAPP_API_USERNAME'),
            'password' => getenv('WHATSAPP_API_PASSWORD'),
            'auth_type' => 'basic',
            'timeout' => 15,
            'retry_attempts' => 2
        ],
        'gdrive' => [
            'base_url' => getenv('GDRIVE_API_URL'),
            'api_key' => getenv('GDRIVE_API_KEY'),
            'auth_type' => 'bearer',
            'timeout' => 60,
            'retry_attempts' => 3
        ]
    ],
    'default_timeout' => 30,
    'max_retries' => 3,
    'retry_delay' => 1000 // milisegundos
];