<?php
class AdmisionController {
    private $admisionModel;
    private $tallerModel;
    private $validationHelper;

    public function __construct() {
        $this->admisionModel = new AdmisionModel();
        $this->tallerModel = new TallerModel();
        $this->validationHelper = new ValidationHelper();
    }

    public function registrarEstudiante() {
        try {
            $datos = $this->validarDatos($_POST);
            $estudiante_id = $this->admisionModel->registrarEstudiante($datos);
            
            createLog($_SESSION['user_id'], 'admision', "Registro estudiante: {$datos['dni']}");
            
            echo json_encode([
                'success' => true,
                'estudiante_id' => $estudiante_id,
                'message' => 'Estudiante registrado exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function validarDatos($datos) {
        $errores = [];

        if (!$this->validationHelper->validarDNI($datos['dni'])) {
            $errores[] = "DNI inválido";
        }

        if (!$this->validarEdad($datos['edad'])) {
            $errores[] = "Edad no válida para el taller";
        }

        if (!in_array($datos['genero'], ['H', 'M'])) {
            $errores[] = "Género no válido";
        }

        if (!$this->tallerModel->verificarDisponibilidad($datos['taller_id'])) {
            $errores[] = "Taller sin cupos disponibles";
        }

        if (!empty($errores)) {
            throw new Exception(implode(", ", $errores));
        }

        return $this->validationHelper->sanitizarInput($datos);
    }

    private function validarEdad($edad) {
        return is_numeric($edad) && $edad >= 5 && $edad <= 17;
    }
}