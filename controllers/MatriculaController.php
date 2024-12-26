<?php
/**
 * Controlador de Matrículas
 * Ruta: Controllers/MatriculaController.php
 */

class MatriculaController {
    private $model;
    private $tallerModel;
    private $horarioModel;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new MatriculaModel($db);
        $this->tallerModel = new TallerModel($db);
        $this->horarioModel = new HorarioModel($db);
        $this->logger = ActivityLogger::getInstance();
    }

    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $data = $this->validateData($_POST);
            
            // Validar cupo disponible
            if (!$this->validateCupo($data['taller_id'])) {
                throw new Exception('No hay cupos disponibles en este taller');
            }

            // Validar conflictos de horario
            if ($this->hasHorarioConflict($data['estudiante_id'], $data['taller_id'])) {
                throw new Exception('Existe conflicto de horarios con otro taller');
            }

            // Validar pago
            if (!$this->validatePago($data['estudiante_id'], $data['taller_id'])) {
                throw new Exception('Debe realizar el pago antes de completar la matrícula');
            }

            $id = $this->model->create($data);
            $this->logger->info('Matrícula creada', ['id' => $id]);

            return json_encode([
                'success' => true,
                'message' => 'Matrícula registrada exitosamente',
                'id' => $id
            ]);

        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function validateData($data) {
        return [
            'estudiante_id' => filter_var($data['estudiante_id'], FILTER_VALIDATE_INT),
            'taller_id' => filter_var($data['taller_id'], FILTER_VALIDATE_INT),
            'turno' => filter_var($data['turno'], FILTER_SANITIZE_STRING),
            'horario_id' => filter_var($data['horario_id'], FILTER_VALIDATE_INT)
        ];
    }

    private function validateCupo($tallerId) {
        $taller = $this->tallerModel->getById($tallerId);
        $matriculados = $this->model->getCountMatriculadosByTaller($tallerId);
        
        return $matriculados < $taller['capacidad_maxima'];
    }

    private function hasHorarioConflict($estudianteId, $tallerId) {
        $nuevoHorario = $this->horarioModel->getHorarioByTaller($tallerId);
        $horariosEstudiante = $this->model->getHorariosEstudiante($estudianteId);

        foreach ($horariosEstudiante as $horario) {
            if ($this->checkHorarioOverlap($nuevoHorario, $horario)) {
                return true;
            }
        }

        return false;
    }

    private function checkHorarioOverlap($horario1, $horario2) {
        $inicio1 = strtotime($horario1['hora_inicio']);
        $fin1 = strtotime($horario1['hora_fin']);
        $inicio2 = strtotime($horario2['hora_inicio']);
        $fin2 = strtotime($horario2['hora_fin']);

        return ($inicio1 < $fin2 && $fin1 > $inicio2);
    }

    private function validatePago($estudianteId, $tallerId) {
        $pagoModel = new PagoModel($this->db);
        return $pagoModel->verificarPago($estudianteId, $tallerId);
    }

    public function getDisponibilidad() {
        try {
            $tallerId = filter_input(INPUT_GET, 'taller_id', FILTER_VALIDATE_INT);
            
            if (!$tallerId) {
                throw new Exception('Taller no válido');
            }

            $taller = $this->tallerModel->getById($tallerId);
            $matriculados = $this->model->getCountMatriculadosByTaller($tallerId);

            return json_encode([
                'success' => true,
                'capacidad_maxima' => $taller['capacidad_maxima'],
                'matriculados' => $matriculados,
                'disponibles' => $taller['capacidad_maxima'] - $matriculados
            ]);

        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}