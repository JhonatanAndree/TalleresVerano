<?php
require_once '../config/yape.php';

class PagoController {
    private $pagoModel;

    public function __construct() {
        $this->pagoModel = new PagoModel();
    }

    public function procesarPagoYape() {
        try {
            $datos = $this->validarDatosPago($_POST);
            $monto = $this->pagoModel->calcularCostoTalleres($datos['estudiante_id']);
            
            if ($this->verificarPagoYape($datos['codigo_yape'], $monto)) {
                $pago_id = $this->pagoModel->registrarPagoYape([
                    'estudiante_id' => $datos['estudiante_id'],
                    'monto' => $monto,
                    'codigo_yape' => $datos['codigo_yape']
                ]);
                
                $this->pagoModel->confirmarPago($pago_id);
                
                echo json_encode(['success' => true, 'message' => 'Pago confirmado']);
            } else {
                throw new Exception('No se pudo verificar el pago con Yape');
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function verificarPagoYape($codigo, $monto) {
        // Integración con API Yape
        $yape = new YapeAPI(YAPE_API_KEY);
        return $yape->verificarTransaccion($codigo, $monto);
    }

    private function validarDatosPago($datos) {
        // Validaciones
        if (!isset($datos['estudiante_id'], $datos['codigo_yape'])) {
            throw new Exception('Datos incompletos');
        }
        return $datos;
    }
}