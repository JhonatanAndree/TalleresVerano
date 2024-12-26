<?php
class RecursosHumanosController {
    private $rhModel;

    public function __construct() {
        $this->rhModel = new RecursosHumanosModel();
    }

    public function registrarPersonal() {
        try {
            $datos = $this->validarDatosPersonal($_POST);
            $id = $this->rhModel->registrarPersonalApoyo($datos);
            createLog($_SESSION['user_id'], 'rh', 'Registro de personal: ' . $datos['dni']);
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function calcularPago() {
        try {
            if (!checkPermission('Administrador')) {
                throw new Exception('No autorizado');
            }

            $personal_id = sanitizeInput($_POST['personal_id']);
            $monto = floatval($_POST['monto']);
            
            $this->rhModel->calcularPagoPersonal($personal_id, $monto);
            createLog($_SESSION['user_id'], 'rh', 'Cálculo pago personal ID: ' . $personal_id);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function validarDatosPersonal($datos) {
        $requeridos = ['nombres', 'apellidos', 'dni', 'celular', 'direccion', 'contacto_familiar', 'id_sede', 'turno'];
        foreach ($requeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("Campo requerido: $campo");
            }
        }
        return array_map('sanitizeInput', $datos);
    }
}