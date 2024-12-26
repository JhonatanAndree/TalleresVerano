<?php
/**
 * Entry point del sistema
 * Ruta: /index.php
 */

// Carga del autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Carga de configuraciones
$app = require_once __DIR__ . '/Config/app.php';
$security = require_once __DIR__ . '/Config/security.php';

// Inicialización de componentes core
$router = Router::getInstance();
$request = Request::getInstance();
$response = Response::getInstance();
$waf = WAF::getInstance();

try {
    // Validación WAF
    $waf->validateRequest();

    // Configuración de headers de seguridad
    foreach ($security['headers'] as $header => $value) {
        header("$header: $value");
    }

    // Inicialización de sesión
    session_start();

    // Verificación de SSL en producción
    if ($app['env'] === 'production' && !isset($_SERVER['HTTPS'])) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Manejo de la ruta
    $route = $router->resolve(
        $_SERVER['REQUEST_METHOD'],
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
    );

    // Ejecución del controlador
    $response = $route->execute($request);
    $response->send();

} catch (NotFoundException $e) {
    http_response_code(404);
    require_once __DIR__ . '/views/errors/404.php';
} catch (UnauthorizedException $e) {
    http_response_code(403);
    require_once __DIR__ . '/views/errors/403.php';
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    if ($app['debug']) {
        echo $e->getMessage();
    } else {
        require_once __DIR__ . '/views/errors/500.php';
    }
}