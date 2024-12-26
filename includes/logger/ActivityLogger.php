<?php
class ActivityLogger {
    private $db;
    private static $instance = null;

    private function __construct(\PDO $db) {
        $this->db = $db;
    }

    public static function getInstance(\PDO $db = null) {
        if (self::$instance === null && $db !== null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }

    public function log($tipo, $modulo, $descripcion, $datosAnteriores = null, $datosNuevos = null, $usuarioId = null) {
        $usuarioId = $usuarioId ?? ($_SESSION['user']['id'] ?? null);

        $sql = "INSERT INTO historial_cambios (
            tipo_cambio, 
            modulo_afectado, 
            descripcion_cambio, 
            datos_anteriores,
            datos_nuevos,
            usuario_id,
            direccion_ip,
            user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $tipo,
                $modulo,
                $descripcion,
                $datosAnteriores ? json_encode($datosAnteriores) : null,
                $datosNuevos ? json_encode($datosNuevos) : null,
                $usuarioId,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
        } catch (\PDOException $e) {
            error_log("Error de logging: " . $e->getMessage());
            return false;
        }
    }

    public function info($mensaje, $datos = []) {
        return $this->log('INFO', $this->getModuloActual(), $mensaje, null, $datos);
    }

    public function warning($mensaje, $datos = []) {
        return $this->log('WARNING', $this->getModuloActual(), $mensaje, null, $datos);
    }

    public function error($mensaje, $datos = []) {
        return $this->log('ERROR', $this->getModuloActual(), $mensaje, null, $datos);
    }

    public function logCambio($modulo, $id, $datosAnteriores, $datosNuevos) {
        $descripcion = "Modificación en {$modulo} ID: {$id}";
        return $this->log('UPDATE', $modulo, $descripcion, $datosAnteriores, $datosNuevos);
    }

    public function exportarCSV($filtros = []) {
        $where = [];
        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $where[] = "fecha_hora >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $where[] = "fecha_hora <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['tipo'])) {
            $where[] = "tipo_cambio = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['modulo'])) {
            $where[] = "modulo_afectado = ?";
            $params[] = $filtros['modulo'];
        }
        if (!empty($filtros['usuario'])) {
            $where[] = "usuario_id = ?";
            $params[] = $filtros['usuario'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT 
                h.*, 
                u.nombre as usuario_nombre,
                u.apellido as usuario_apellido
            FROM historial_cambios h
            LEFT JOIN usuarios u ON h.usuario_id = u.id
            {$whereClause}
            ORDER BY h.fecha_hora DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error exportando historial: " . $e->getMessage());
            return false;
        }
    }

    private function getModuloActual() {
        $uri = $_SERVER['REQUEST_URI'];
        $partes = explode('/', trim($uri, '/'));
        return $partes[0] ?? 'general';
    }
}