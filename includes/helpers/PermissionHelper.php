<?php
/**
 * Helper para manejo de permisos
 * Ruta: includes/helpers/PermissionHelper.php
 */

class PermissionHelper {
    private static $instance = null;
    private $permissions;
    private $userRole;

    private function __construct() {
        $this->permissions = require __DIR__ . '/../../Config/permissions.php';
        $this->userRole = $_SESSION['user']['rol'] ?? null;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function can($module, $action) {
        if (!$this->userRole) return false;
        
        if ($this->userRole === 'SuperAdmin') return true;

        $rolePermissions = $this->permissions[$this->userRole] ?? [];
        
        if (isset($rolePermissions[$module])) {
            return in_array($action, $rolePermissions[$module]);
        }
        
        return false;
    }

    public function getAllowedModules() {
        if (!$this->userRole) return [];
        
        if ($this->userRole === 'SuperAdmin') {
            return array_keys($this->permissions['SuperAdmin']);
        }
        
        return array_keys($this->permissions[$this->userRole] ?? []);
    }

    public function getAllowedActions($module) {
        if (!$this->userRole) return [];
        
        if ($this->userRole === 'SuperAdmin') {
            return $this->permissions['SuperAdmin']['all'];
        }
        
        return $this->permissions[$this->userRole][$module] ?? [];
    }

    public function requirePermission($module, $action) {
        if (!$this->can($module, $action)) {
            header('HTTP/1.1 403 Forbidden');
            require_once __DIR__ . '/../../views/errors/403.php';
            exit;
        }
    }
}