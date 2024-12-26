<?php
require_once BASE_PATH . '/models/EstudianteModel.php';

class ConsultaController {
    private $estudianteModel;

    public function __construct() {
        $this->estudianteModel = new EstudianteModel();
    }

    public function consultarEstudiante() {
        $dni_estudiante = sanitizeInput($_POST['dni_estudiante']);
        $dni_padre = sanitizeInput($_POST['dni_padre']);

        try {
            $datos = $this->estudianteModel->obtenerPorDNIs($dni_estudiante, $dni_padre);
            
            if ($datos) {
                echo json_encode([
                    'success' => true,
                    'data' => $datos
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron datos'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar la consulta'
            ]);
        }
    }
}