<?php
class DocentePagoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function calcularPagoDocente($docente_id, $mes, $ano) {
        $stmt = $this->db->prepare("
            SELECT 
                d.costo_hora,
                COUNT(DISTINCT h.id) * h.duracion as total_horas
            FROM usuarios d
            JOIN talleres t ON d.id = t.id_docente
            JOIN horarios h ON t.id = h.id_taller
            WHERE d.id = ? AND MONTH(h.fecha) = ? AND YEAR(h.fecha) = ?
            GROUP BY d.id
        ");
        $stmt->execute([$docente_id, $mes, $ano]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarPago($datos) {
        $stmt = $this->db->prepare("
            INSERT INTO pagos_docentes (
                docente_id, monto, mes, ano,
                horas_trabajadas, estado
            ) VALUES (?, ?, ?, ?, ?, 'pendiente')
        ");
        return $stmt->execute([
            $datos['docente_id'],
            $datos['monto'],
            $datos['mes'],
            $datos['ano'],
            $datos['horas_trabajadas']
        ]);
    }
}