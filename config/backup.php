<?php
return [
    'backup_path' => __DIR__ . '/../storage/backups',
    'db_user' => getenv('DB_USERNAME'),
    'db_pass' => getenv('DB_PASSWORD'),
    'db_name' => getenv('DB_DATABASE'),
    'encryption_key' => getenv('BACKUP_ENCRYPTION_KEY'),
    'drive_folder_id' => getenv('BACKUP_DRIVE_FOLDER_ID'),
    'schedule' => [
        'time' => '19:00',
        'timezone' => 'America/Lima',
        'retention_days' => 30
    ]
];