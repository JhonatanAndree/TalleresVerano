<?php
class PersonalApoyoController {
    private $personalModel;

    public function __construct() {
        $this->personalModel = new PersonalApoyoModel();
    }

    public function procesarSolicitud() {
        $accion = $_REQUEST['action'] ?? '';
        
        switch($accion) {
            case 'registrar':
                return $this->registrarPersonal();
            case 'actualizar':
                return $this->actualizarPersonal();
            case 'listar':
                return $this->obtenerPersonal();
            case 'registrarPago':
                return $this->registrarPago();
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }

    private function registrarPersonal() {
        try {
            $datos = $this->validarDatosPersonal($_POST);
            $this->personalModel->registrarPersonal($datos);
            createLog($_SESSION['user_id'], 'personal', "Personal registrado: {$datos['dni']}");
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}