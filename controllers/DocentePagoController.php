<?php
class DocentePagoController {
    private $docentePagoModel;

    public function __construct() {
        $this->docentePagoModel = new DocentePagoModel();
    }

    public function calcularPago() {
        try {
            $docente_id = sanitizeInput($_POST['docente_id']);
            $mes = sanitizeInput($_POST['mes']);
            $ano = sanitizeInput($_POST['ano']);

            $calculo = $this->docentePagoModel->calcularPagoDocente($docente_id, $mes, $ano);
            
            $pago = [
                'docente_id' => $docente_id,
                'monto' => $calculo['costo_hora'] * $calculo['total_horas'],
                'mes' => $mes,
                'ano' => $ano,
                'horas_trabajadas' => $calculo['total_horas']
            ];

            $this->docentePagoModel->registrarPago($pago);
            createLog($_SESSION['user_id'], 'pago_docente', "Pago registrado: $docente_id");

            echo json_encode(['success' => true, 'data' => $pago]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}