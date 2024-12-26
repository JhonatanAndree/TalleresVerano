<?php
class AdmisionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function registrarEstudiante($datos) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO estudiantes (
                    nombre, apellidos, dni, edad, genero,
                    id_padre, registrador_id, fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $datos['nombre'],
                $datos['apellidos'],
                $datos['dni'],
                $datos['edad'],
                $datos['genero'],
                $datos['id_padre'],
                $_SESSION['user_id']
            ]);

            $estudiante_id = $this->db->lastInsertId();
            $this->asignarTaller($estudiante_id, $datos['taller_id']);
            
            $this->db->commit();
            return $estudiante_id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function asignarTaller($estudiante_id, $taller_id) {
        if (!$this->validarCupoDisponible($taller_id)) {
            throw new Exception("No hay cupos disponibles en este taller");
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO estudiantes_talleres (
                estudiante_id, taller_id, fecha_asignacion
            ) VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$estudiante_id, $taller_id]);
    }
}