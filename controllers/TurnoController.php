<?php
class TurnoController {
    private $turnoModel;

    public function __construct() {
        $this->turnoModel = new TurnoModel();
    }

    public function getTurnos() {
        try {
            $turnos = $this->turnoModel->getTurnos();
            echo json_encode(['success' => true, 'data' => $turnos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function verificarDisponibilidad() {
        $datos = $this->validarDatos($_POST);
        $disponible = $this->turnoModel->validarDisponibilidad(
            $datos['turno_id'],
            $datos['docente_id'],
            $datos['fecha']
        );
        echo json_encode(['success' => true, 'disponible' => $disponible]);
    }
}