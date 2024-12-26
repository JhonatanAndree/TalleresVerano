<?php
/**
 * Modelo para gestión de reportes
 * Ruta: Models/ReporteModel.php
 */

class ReporteModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getReportData($filters) {
        $where = [];
        $params = [];

        if ($filters['fecha_inicio']) {
            $where[] = "fecha >= ?";
            $params[] = $filters['fecha_inicio'];
        }
        if ($filters['fecha_fin']) {
            $where[] = "fecha <= ?";
            $params[] = $filters['fecha_fin'];
        }
        if ($filters['tipo']) {
            $where[] = "tipo = ?";
            $params[] = $filters['tipo'];
        }
        if ($filters['sede']) {
            $where[] = "sede_id = ?";
            $params[] = $filters['sede'];
        }
        if ($filters['taller']) {
            $where[] = "taller_id = ?";
            $params[] = $filters['taller'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Calcular paginación
        $page = max(1, $filters['page']);
        $perPage = $filters['per_page'];
        $offset = ($page - 1) * $perPage;

        // Obtener total de registros
        $sqlCount = "SELECT COUNT(*) as total FROM vista_reporte $whereClause";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

        // Obtener datos paginados
        $sql = "SELECT * FROM vista_reporte $whereClause 
                ORDER BY fecha DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    public function getTotalEstudiantes($anoFiscal) {
        $sql = "SELECT COUNT(*) as total 
                FROM estudiantes e 
                JOIN matriculas m ON e.id = m.estudiante_id 
                WHERE YEAR(m.fecha) = ? AND e.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anoFiscal]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalIngresos($anoFiscal) {
        $sql = "SELECT SUM(monto) as total 
                FROM pagos 
                WHERE YEAR(fecha) = ? AND estado = 'completado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anoFiscal]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function getTalleresPopulares($anoFiscal) {
        $sql = "SELECT t.nombre, COUNT(m.id) as total_estudiantes 
                FROM talleres t 
                JOIN matriculas m ON t.id = m.taller_id 
                WHERE YEAR(m.fecha) = ? AND t.deleted_at IS NULL 
                GROUP BY t.id 
                ORDER BY total_estudiantes DESC 
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anoFiscal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasMensuales($anoFiscal) {
        $sql = "SELECT 
                    MONTH(m.fecha) as mes,
                    COUNT(DISTINCT m.estudiante_id) as total_estudiantes,
                    COUNT(DISTINCT m.taller_id) as total_talleres,
                    SUM(p.monto) as total_ingresos
                FROM matriculas m
                LEFT JOIN pagos p ON m.id = p.matricula_id
                WHERE YEAR(m.fecha) = ?
                GROUP BY MONTH(m.fecha)
                ORDER BY mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anoFiscal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReporteIngresos($filters) {
        $sql = "SELECT 
                    p.fecha,
                    e.nombre as estudiante,
                    t.nombre as taller,
                    s.nombre as sede,
                    p.monto,
                    p.metodo_pago,
                    p.estado
                FROM pagos p
                JOIN estudiantes e ON p.estudiante_id = e.id
                JOIN talleres t ON p.taller_id = t.id
                JOIN sedes s ON t.sede_id = s.id
                WHERE p.deleted_at IS NULL";

        $params = [];
        if ($filters['fecha_inicio']) {
            $sql .= " AND p.fecha >= ?";
            $params[] = $filters['fecha_inicio'];
        }
        if ($filters['fecha_fin']) {
            $sql .= " AND p.fecha <= ?";
            $params[] = $filters['fecha_fin'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReporteAsistencia($filters) {
        $sql = "SELECT 
                    a.fecha,
                    e.nombre as estudiante,
                    t.nombre as taller,
                    d.nombre as docente,
                    a.estado,
                    a.observaciones
                FROM asistencias a
                JOIN estudiantes e ON a.estudiante_id = e.id
                JOIN talleres t ON a.taller_id = t.id
                JOIN usuarios d ON t.docente_id = d.id
                WHERE a.deleted_at IS NULL";

        $params = [];
        if ($filters['fecha_inicio']) {
            $sql .= " AND a.fecha >= ?";
            $params[] = $filters['fecha_inicio'];
        }
        if ($filters['fecha_fin']) {
            $sql .= " AND a.fecha <= ?";
            $params[] = $filters['fecha_fin'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSedeNombre($id) {
        $stmt = $this->db->prepare("SELECT nombre FROM sedes WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nombre'] : null;
    }

    public function getTallerNombre($id) {
        $stmt = $this->db->prepare("SELECT nombre FROM talleres WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nombre'] : null;
    }
}