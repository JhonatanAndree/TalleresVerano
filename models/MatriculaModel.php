<?php
/**
 * Modelo de Matrículas
 * Ruta: Models/MatriculaModel.php
 */

class MatriculaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO matriculas (estudiante_id, taller_id, turno, horario_id, created_at) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['estudiante_id'],
            $data['taller_id'],
            $data['turno'],
            $data['horario_id']
        ]);

        return $this->db->lastInsertId();
    }

    public function getCountMatriculadosByTaller($tallerId) {
        $sql = "SELECT COUNT(*) as total 
                FROM matriculas 
                WHERE taller_id = ? AND deleted_at IS NULL";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tallerId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getHorariosEstudiante($estudianteId) {
        $sql = "SELECT h.* 
                FROM horarios h 
                JOIN matriculas m ON h.id = m.horario_id 
                WHERE m.estudiante_id = ? AND m.deleted_at IS NULL";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estudianteId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMatriculasByEstudiante($estudianteId) {
        $sql = "SELECT m.*, t.nombre as taller_nombre, h.hora_inicio, h.hora_fin 
                FROM matriculas m 
                JOIN talleres t ON m.taller_id = t.id 
                JOIN horarios h ON m.horario_id = h.id 
                WHERE m.estudiante_id = ? AND m.deleted_at IS NULL";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estudianteId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $sql = "UPDATE matriculas SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}