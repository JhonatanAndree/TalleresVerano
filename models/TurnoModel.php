<?php
class TurnoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTurnos() {
        return $this->db->query("SELECT * FROM turnos WHERE activo = 1")->fetchAll();
    }

    public function validarDisponibilidad($turno_id, $docente_id, $fecha) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM asignaciones_turno 
            WHERE turno_id = ? AND docente_id = ? 
            AND fecha = ? AND activo = 1
        ");
        $stmt->execute([$turno_id, $docente_id, $fecha]);
        return $stmt->fetchColumn() == 0;
    }
}