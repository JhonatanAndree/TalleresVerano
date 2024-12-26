<?php
// URLs y Rutas Base
define('BASE_URL', 'https://muniporvenir.gob.pe/talleresdeverano');
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_talleres');
define('DB_USER', 'usuario_db');
define('DB_PASS', 'password_db');
define('DB_CHARSET', 'utf8mb4');

// Configuración de Sesión
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'TALLERES_SESSION');

// Seguridad
define('HASH_COST', 12);
define('TOKEN_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 3);

// Configuración de Archivos
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Zona Horaria
define('TIMEZONE', 'America/Lima');
date_default_timezone_set(TIMEZONE);☺