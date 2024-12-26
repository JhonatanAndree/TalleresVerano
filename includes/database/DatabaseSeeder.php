<?php
/**
 * Sembrador de datos iniciales
 * Ruta: includes/database/DatabaseSeeder.php
 */

class DatabaseSeeder {
    private $db;
    private $security;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->security = SecurityHelper::getInstance();
    }

    public function run() {
        $this->seedRoles();
        $this->seedPermisos();
        $this->seedSuperAdmin();
        $this->seedConfiguracionInicial();
    }

    private function seedRoles() {
        $roles = [
            ['nombre' => 'SuperAdmin', 'descripcion' => 'Control total del sistema'],
            ['nombre' => 'Administrador', 'descripcion' => 'Gestión administrativa'],
            ['nombre' => 'Docente', 'descripcion' => 'Gestión de talleres'],
            ['nombre' => 'Registrador', 'descripcion' => 'Registro de matrículas'],
            ['nombre' => 'Padre', 'descripcion' => 'Consulta de estudiantes']
        ];

        $stmt = $this->db->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
        foreach ($roles as $rol) {
            $stmt->execute([$rol['nombre'], $rol['descripcion']]);
        }
    }

    private function seedPermisos() {
        $permisos = [
            // Administración
            ['modulo' => 'usuarios', 'accion' => 'crear'],
            ['modulo' => 'usuarios', 'accion' => 'ver'],
            ['modulo' => 'usuarios', 'accion' => 'editar'],
            ['modulo' => 'usuarios', 'accion' => 'eliminar'],
            
            // Talleres
            ['modulo' => 'talleres', 'accion' => 'crear'],
            ['modulo' => 'talleres', 'accion' => 'ver'],
            ['modulo' => 'talleres', 'accion' => 'editar'],
            ['modulo' => 'talleres', 'accion' => 'eliminar'],
            
            // Matrículas
            ['modulo' => 'matriculas', 'accion' => 'crear'],
            ['modulo' => 'matriculas', 'accion' => 'ver'],
            ['modulo' => 'matriculas', 'accion' => 'editar'],
            ['modulo' => 'matriculas', 'accion' => 'eliminar']
        ];

        $stmt = $this->db->prepare("INSERT INTO permisos (modulo, accion) VALUES (?, ?)");
        foreach ($permisos as $permiso) {
            $stmt->execute([$permiso['modulo'], $permiso['accion']]);
        }
    }

    private function seedSuperAdmin() {
        $password = $this->security->hashPassword('admin123');
        
        $sql = "INSERT INTO usuarios (
            nombre, 
            apellido, 
            email, 
            contrasena, 
            rol,
            activo
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $this->db->prepare($sql)->execute([
            'Admin',
            'Sistema',
            'admin@muniporvenir.gob.pe',
            $password,
            'SuperAdmin',
            1
        ]);
    }

    private function seedConfiguracionInicial() {
        $config = [
            'nombre_sistema' => 'Sistema de Talleres de Verano',
            'año_fiscal' => date('Y'),
            'moneda' => 'PEN',
            'zona_horaria' => 'America/Lima',
            'correo_soporte' => 'soporte@muniporvenir.gob.pe',
            'telefono_soporte' => '123456789'
        ];

        $sql = "INSERT INTO configuracion (clave, valor) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($config as $clave => $valor) {
            $stmt->execute([$clave, $valor]);
        }
    }
}