<?php
/**
 * Sistema de permisos
 * Ruta: includes/security/PermissionSystem.php
 */

class PermissionSystem {
    private $db;
    private $session;
    private $logger;
    private static $instance = null;
    private $permissions = [];

    private function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->session = Session::getInstance();
        $this->logger = ActivityLogger::getInstance();
        $this->loadPermissions();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadPermissions() {
        if ($this->session->has('user.permissions')) {
            $this->permissions = $this->session->get('user.permissions');
            return;
        }

        $userId = $this->session->get('user.id');
        if (!$userId) return;

        $sql = "SELECT p.nombre, p.descripcion 
                FROM permisos p 
                JOIN roles_permisos rp ON p.id = rp.permiso_id 
                JOIN roles r ON rp.rol_id = r.id 
                JOIN usuarios u ON u.rol_id = r.id 
                WHERE u.id = ? AND p.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $this->permissions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->session->set('user.permissions', $this->permissions);
    }

    public function can($permission) {
        // SuperAdmin tiene todos los permisos
        if ($this->session->get('user.rol') === 'SuperAdmin') {
            return true;
        }

        return isset($this->permissions[$permission]);
    }

    public function hasRole($role) {
        return $this->session->get('user.rol') === $role;
    }

    public function hasAnyRole(array $roles) {
        return in_array($this->session->get('user.rol'), $roles);
    }

    public function getPermissions() {
        return $this->permissions;
    }

    public function refreshPermissions() {
        $this->session->remove('user.permissions');
        $this->loadPermissions();
    }
}