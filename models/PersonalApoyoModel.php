<?php
class PersonalApoyoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function registrarPersonal($datos) {
        $stmt = $this->db->prepare("
            INSERT INTO personal_apoyo (
                nombres, apellidos, dni, celular,
                direccion, contacto_familiar, id_sede,
                turno, activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        return $stmt->execute([
            $datos['nombres'],
            $datos['apellidos'],
            $datos['dni'],
            $datos['celular'],
            $datos['direccion'],
            $datos['contacto_familiar'],
            $datos['id_sede'],
            $datos['turno']
        ]);
    }

    public function actualizarPersonal($id, $datos) {
        $stmt = $this->db->prepare("
            UPDATE personal_apoyo
            SET nombres = ?, apellidos = ?, celular = ?,
                direccion = ?, contacto_familiar = ?,
                id_sede = ?, turno = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $datos['nombres'],
            $datos['apellidos'],
            $datos['celular'],
            $datos['direccion'],
            $datos['contacto_familiar'],
            $datos['id_sede'],
            $datos['turno'],
            $id
        ]);
    }

    public function obtenerPersonalPorSede($sede_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, s.nombre as sede_nombre
            FROM personal_apoyo p
            JOIN sedes s ON p.id_sede = s.id
            WHERE p.id_sede = ? AND p.activo = 1
        ");
        $stmt->execute([$sede_id]);
        return $stmt->fetchAll();
    }

    public function registrarPago($personal_id, $monto, $mes, $ano) {
        $stmt = $this->db->prepare("
            INSERT INTO pagos_personal (
                personal_id, monto, mes, ano,
                fecha_pago, estado
            ) VALUES (?, ?, ?, ?, NOW(), 'pendiente')
        ");
        return $stmt->execute([$personal_id, $monto, $mes, $ano]);
    }
}