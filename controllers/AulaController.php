<?php
class AulaController {
    private $aulaModel;

    public function __construct() {
        $this->aulaModel = new AulaModel();
    }

    public function getAulasDisponibles() {
        try {
            $sede_id = sanitizeInput($_GET['sede_id']);
            $horario_id = sanitizeInput($_GET['horario_id']);
            
            $aulas = $this->aulaModel->getAulasDisponibles($sede_id, $horario_id);
            echo json_encode(['success' => true, 'data' => $aulas]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function validarCapacidad() {
        try {
            if (!$this->aulaModel->validarCapacidad(
                $_POST['aula_id'],
                $_POST['estudiantes']
            )) {
                throw new Exception('Capacidad del aula excedida');
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}