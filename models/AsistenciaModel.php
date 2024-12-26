<?php
/**
 * Modelo de Asistencias
 * Ruta: Models/AsistenciaModel.php
 */

class AsistenciaModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'asistencias';
    protected $fillable = [
        'estudiante_id',
        'taller_id',
        'fecha',
        'estado',
        'observaciones'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAsistenciasPorTaller($tallerId, $fecha = null) {
        $sql = "SELECT a.*, e.nombre as estudiante_nombre, e.apellido as estudiante_apellido 
                FROM asistencias a
                JOIN estudiantes e ON a.estudiante_id = e.id
                WHERE a.taller_id = ? AND a.deleted_at IS NULL";
        
        $params = [$tallerId];
        if ($fecha) {
            $sql .= " AND a.fecha = ?";
            $params[] = $fecha;
        }
        
        $sql .= " ORDER BY e.apellido, e.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function registrarAsistenciaGrupal($tallerId, $fecha, $asistencias) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO asistencias (estudiante_id, taller_id, fecha, estado, observaciones) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            foreach ($asistencias as $asistencia) {
                $stmt->execute([
                    $asistencia['estudiante_id'],
                    $tallerId,
                    $fecha,
                    $asistencia['estado'],
                    $asistencia['observaciones'] ?? null
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getEstadisticasAsistencia($tallerId, $desde = null, $hasta = null) {
        $sql = "SELECT 
                    e.id as estudiante_id,
                    e.nombre,
                    e.apellido,
                    COUNT(CASE WHEN a.estado = 'presente' THEN 1 END) as presentes,
                    COUNT(CASE WHEN a.estado = 'ausente' THEN 1 END) as ausentes,
                    COUNT(CASE WHEN a.estado = 'tardanza' THEN 1 END) as tardanzas,
                    COUNT(*) as total_clases,
                    (COUNT(CASE WHEN a.estado = 'presente' THEN 1 END) * 100.0 / COUNT(*)) as porcentaje_asistencia
                FROM estudiantes e
                LEFT JOIN asistencias a ON e.id = a.estudiante_id
                WHERE a.taller_id = ? 
                AND a.deleted_at IS NULL";

        $params = [$tallerId];
        
        if ($desde) {
            $sql .= " AND a.fecha >= ?";
            $params[] = $desde;
        }
        if ($hasta) {
            $sql .= " AND a.fecha <= ?";
            $params[] = $hasta;
        }

        $sql .= " GROUP BY e.id, e.nombre, e.apellido
                  ORDER BY porcentaje_asistencia DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}