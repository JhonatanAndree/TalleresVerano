<?php
class RecursosHumanosModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function registrarPersonalApoyo($datos) {
        $stmt = $this->db->prepare("
            INSERT INTO personal_apoyo (
                nombres, apellidos, dni, celular, 
                direccion, contacto_familiar, id_sede, 
                turno, activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        return $stmt->execute([
            $datos['nombres'], $datos['apellidos'],
            $datos['dni'], $datos['celular'],
            $datos['direccion'], $datos['contacto_familiar'],
            $datos['id_sede'], $datos['turno']
        ]);
    }

    public function calcularPagoPersonal($personal_id, $monto_total) {
        $stmt = $this->db->prepare("
            UPDATE personal_apoyo 
            SET monto_pago = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$monto_total, $personal_id]);
    }
}