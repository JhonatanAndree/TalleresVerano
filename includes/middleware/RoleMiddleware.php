<?php
/**
 * Middleware de roles y permisos
 * Ruta: includes/middleware/RoleMiddleware.php
 */

class RoleMiddleware {
    private $session;
    private $security;
    private $logger;
    private $allowedRoles;

    public function __construct(array $allowedRoles = []) {
        $this->session = Session::getInstance();
        $this->security = SecurityHelper::getInstance();
        $this->logger = ActivityLogger::getInstance();
        $this->allowedRoles = $allowedRoles;
    }

    public function handle() {
        $userRole = $this->session->get('user.rol');
        
        // Verificar si el rol tiene acceso
        if (!$this->hasAccess($userRole)) {
            $this->logger->warning('Intento de acceso no autorizado', [
                'user_id' => $this->session->get('user.id'),
                'role' => $userRole,
                'required_roles' => $this->allowedRoles
            ]);
            
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No autorizado']);
                exit;
            }

            header('Location: /403');
            exit;
        }

        return true;
    }

    private function hasAccess($userRole) {
        // SuperAdmin siempre tiene acceso
        if ($userRole === 'SuperAdmin') {
            return true;
        }

        return empty($this->allowedRoles) || in_array($userRole, $this->allowedRoles);
    }

    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}