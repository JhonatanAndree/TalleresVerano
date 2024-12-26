<?php
class PaymentProcessor {
    private $db;
    private $logger;
    private $yapeService;
    private $notificationService;

    public function __construct() {
        $this->db = require __DIR__ . '/../../Config/db.php';
        $this->logger = ActivityLogger::getInstance();
        $this->yapeService = new YapeService();
        $this->notificationService = new NotificationService();
    }

    public function procesarPago($data) {
        try {
            $this->db->beginTransaction();

            $pagoId = $this->crearRegistroPago($data);
            $resultado = $this->yapeService->initializePayment([
                'amount' => $data['monto'],
                'payment_id' => $pagoId,
                'concept' => $data['concepto']
            ]);

            if ($resultado['success']) {
                $this->actualizarEstadoPago($pagoId, 'pendiente', $resultado['transaction_id']);
                $this->db->commit();
                return [
                    'success' => true,
                    'payment_id' => $pagoId,
                    'qr_data' => $resultado['qr_data']
                ];
            }

            throw new Exception('Error al inicializar el pago');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Error en procesamiento de pago', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    private function crearRegistroPago($data) {
        $sql = "INSERT INTO pagos (
                    estudiante_id, 
                    taller_id, 
                    monto, 
                    concepto, 
                    estado, 
                    created_at
                ) VALUES (?, ?, ?, ?, 'inicial', CURRENT_TIMESTAMP)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['estudiante_id'],
            $data['taller_id'],
            $data['monto'],
            $data['concepto']
        ]);

        return $this->db->lastInsertId();
    }

    private function actualizarEstadoPago($pagoId, $estado, $transactionId = null) {
        $sql = "UPDATE pagos 
                SET estado = ?, 
                    transaction_id = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $transactionId, $pagoId]);
    }

    public function verificarPago($transactionId) {
        try {
            $resultado = $this->yapeService->checkStatus($transactionId);
            
            if ($resultado['status'] === 'completed') {
                $this->completarPago($transactionId);
                return ['success' => true, 'status' => 'completed'];
            }

            return ['success' => true, 'status' => $resultado['status']];
        } catch (Exception $e) {
            $this->logger->error('Error verificando pago', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            throw $e;
        }
    }

    private function completarPago($transactionId) {
        $this->db->beginTransaction();
        try {
            $sql = "SELECT id, estudiante_id, taller_id FROM pagos WHERE transaction_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$transactionId]);
            $pago = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pago) {
                throw new Exception('Pago no encontrado');
            }

            $this->actualizarEstadoPago($pago['id'], 'completado');
            $this->actualizarMatricula($pago['estudiante_id'], $pago['taller_id']);
            $this->notificationService->notificarPagoCompletado($pago['id']);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function actualizarMatricula($estudianteId, $tallerId) {
        $sql = "UPDATE matriculas 
                SET estado = 'activo', 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE estudiante_id = ? 
                AND taller_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estudianteId, $tallerId]);
    }
}