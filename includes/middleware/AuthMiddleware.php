<?php
require_once __DIR__ . '/../../Config/security.php';
require_once __DIR__ . '/../../Config/permissions.php';

class AuthMiddleware {
    private $security;
    private $permissions;
    private static $publicRoutes = [
        '/login',
        '/reset-password',
        '/consulta-padres',
        '/assets/',
        '/public/'
    ];

    public function __construct() {
        $this->security = SecurityHelper::getInstance();
        $this->permissions = require __DIR__ . '/../../Config/permissions.php';
    }

    public function handle() {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($this->isPublicRoute($currentPath)) {
            return true;
        }

        if (!$this->validateSession()) {
            $this->redirectToLogin();
        }

        if (!$this->validatePermissions($currentPath)) {
            $this->handleUnauthorized();
        }

        $this->security->setSecurityHeaders();
        return true;
    }

    private function validateSession() {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['token'])) {
            return false;
        }

        return $this->security->validateJWT($_SESSION['user']['token']);
    }

    private function validatePermissions($path) {
        $userRole = $_SESSION['user']['rol'];
        $modulePermissions = $this->permissions[$userRole] ?? [];

        foreach ($modulePermissions as $module => $actions) {
            if (strpos($path, "/$module/") === 0) {
                $action = $this->getActionFromPath($path);
                return in_array($action, $actions);
            }
        }

        return false;
    }

    private function getActionFromPath($path) {
        $parts = explode('/', trim($path, '/'));
        return end($parts) ?: 'index';
    }

    private function isPublicRoute($path) {
        foreach (self::$publicRoutes as $publicRoute) {
            if (strpos($path, $publicRoute) === 0) {
                return true;
            }
        }
        return false;
    }

    private function redirectToLogin() {
        header('Location: /login');
        exit;
    }

    private function handleUnauthorized() {
        http_response_code(403);
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para acceder a este recurso']);
        } else {
            require_once __DIR__ . '/../../views/errors/403.php';
        }
        exit;
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

// Uso en index.php
$middleware = new AuthMiddleware();
$middleware->handle();