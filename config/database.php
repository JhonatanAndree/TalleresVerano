<?php
/**
 * Configuración de la base de datos
 * Ruta: Config/database.php 
 */

return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST', 'localhost'),
            'database' => getenv('DB_DATABASE', 'sistema_talleres'),
            'username' => getenv('DB_USERNAME', 'root'),
            'password' => getenv('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'port' => getenv('DB_PORT', '3306'),
            'strict' => true,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
            'timezone' => '-05:00'
        ]
    ],
    
    'migrations' => 'migrations',
    
    'backup' => [
        'enabled' => true,
        'schedule' => '0 19 * * *', // 7:00 PM todos los días
        'path' => storage_path('backups'),
        'compression' => true,
        'retention' => [
            'daily' => 7,    // mantener 7 días
            'weekly' => 4,   // mantener 4 semanas
            'monthly' => 3,  // mantener 3 meses
        ]
    ]
];