<?php
/**
 * Modelo del Dashboard
 * Ruta: Models/DashboardModel.php
 */

class DashboardModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalEstudiantes() {
        $sql = "SELECT COUNT(*) as total FROM estudiantes WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalIngresos() {
        $sql = "SELECT COALESCE(SUM(monto), 0) as total 
                FROM pagos 
                WHERE estado = 'completado' 
                AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return number_format($stmt->fetch(PDO::FETCH_ASSOC)['total'], 2);
    }

    public function getTotalTalleresActivos() {
        $sql = "SELECT COUNT(*) as total 
                FROM talleres 
                WHERE deleted_at IS NULL 
                AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getAsistenciaPromedio() {
        $sql = "SELECT AVG(porcentaje) as promedio 
                FROM (
                    SELECT 
                        taller_id,
                        (COUNT(CASE WHEN estado = 'presente' THEN 1 END) * 100.0 / COUNT(*)) as porcentaje
                    FROM asistencias
                    WHERE fecha >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    AND deleted_at IS NULL
                    GROUP BY taller_id
                ) as promedios";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'], 1);
    }

    public function getInscripcionesPorTaller() {
        $sql = "SELECT 
                    t.nombre as taller,
                    COUNT(m.id) as total
                FROM talleres t
                LEFT JOIN matriculas m ON t.id = m.taller_id
                WHERE t.deleted_at IS NULL
                AND m.deleted_at IS NULL
                GROUP BY t.id, t.nombre
                ORDER BY total DESC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIngresosMensuales() {
        $sql = "SELECT 
                    DATE_FORMAT(fecha, '%Y-%m') as mes,
                    COALESCE(SUM(monto), 0) as total
                FROM pagos
                WHERE estado = 'completado'
                AND deleted_at IS NULL
                AND fecha >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                ORDER BY mes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistribucionPorSede() {
        $sql = "SELECT 
                    s.nombre as sede,
                    COUNT(DISTINCT m.estudiante_id) as total
                FROM sedes s
                LEFT JOIN talleres t ON s.id = t.sede_id
                LEFT JOIN matriculas m ON t.id = m.taller_id
                WHERE s.deleted_at IS NULL
                AND m.deleted_at IS NULL
                GROUP BY s.id, s.nombre
                ORDER BY total DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAsistenciaSemanal() {
        $sql = "SELECT 
                    fecha,
                    (COUNT(CASE WHEN estado = 'presente' THEN 1 END) * 100.0 / COUNT(*)) as porcentaje
                FROM asistencias
                WHERE fecha >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                AND deleted_at IS NULL
                GROUP BY fecha
                ORDER BY fecha";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}