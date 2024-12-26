<?php
/**
 * Configuración de servicios externos
 * Ruta: Config/services.php
 */

return [
    'whatsapp' => [
        'api_key' => getenv('WHATSAPP_API_KEY'),
        'api_url' => getenv('WHATSAPP_API_URL'),
        'number_id' => getenv('WHATSAPP_NUMBER_ID'),
        'verify_token' => getenv('WHATSAPP_VERIFY_TOKEN'),
        'templates' => [
            'recordatorio_pago' => [
                'name' => 'recordatorio_pago',
                'language' => 'es'
            ],
            'confirmacion_matricula' => [
                'name' => 'confirmacion_matricula',
                'language' => 'es'
            ]
        ]
    ],
    'email' => [
        'from_address' => getenv('MAIL_FROM_ADDRESS'),
        'from_name' => getenv('MAIL_FROM_NAME'),
        'smtp_host' => getenv('MAIL_HOST'),
        'smtp_port' => getenv('MAIL_PORT'),
        'smtp_user' => getenv('MAIL_USERNAME'),
        'smtp_pass' => getenv('MAIL_PASSWORD'),
        'encryption' => getenv('MAIL_ENCRYPTION')
    ],
    'google_drive' => [
        'client_id' => getenv('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => getenv('GOOGLE_DRIVE_CLIENT_SECRET'),
        'redirect_uri' => getenv('GOOGLE_DRIVE_REDIRECT_URI'),
        'folder_id' => getenv('GOOGLE_DRIVE_FOLDER_ID')
    ]
];