<?php
/**
 * Configuración de correo electrónico
 * Ruta: Config/mail.php
 */

return [
    'host' => getenv('MAIL_HOST'),
    'port' => getenv('MAIL_PORT'),
    'username' => getenv('MAIL_USERNAME'),
    'password' => getenv('MAIL_PASSWORD'),
    'from_address' => getenv('MAIL_FROM_ADDRESS'),
    'from_name' => getenv('MAIL_FROM_NAME'),
    'contact_phone' => getenv('CONTACT_PHONE'),
    'contact_email' => getenv('CONTACT_EMAIL'),
    'encryption' => 'tls'
];