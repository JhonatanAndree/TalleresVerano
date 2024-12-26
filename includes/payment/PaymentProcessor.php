<?php
/**
 * Procesador de pagos
 * Ruta: includes/payment/PaymentProcessor.php
 */

class PaymentProcessor {
    private $db;
    private $yapeService;
    private $logger;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->yapeService = new YapeService();
        $this->logger = ActivityLogger::getInstance();
        $this->config = require __DIR__ . '/../../Config/services.php';
    }

    public function processPayment($matriculaId, $method = 'yape') {
        try {
            $this->db->beginTransaction();
            
            $matricula = $this->validateAndGetMatricula($matriculaId);
            $monto = $this->calculateAmount($matricula);
            $transactionId = $this->generateTransactionId();
            
            $paymentData = [
                'amount' => $monto,
                'transaction_id' => $transactionId,
                'matricula_id' => $matriculaId,
                'estudiante_id' => $matricula['estudiante_id'],
                'concepto' => "Pago taller: {$matricula['taller_nombre']}"
            ];

            // Registrar intento de pago
            $this->registerPaymentAttempt($paymentData);

            $result = match($method) {
                'yape' => $this->yapeService->initializePayment($paymentData),
                default => throw new Exception('Método de pago no soportado')
            };

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Error procesando pago', [
                'error' => $e->getMessage(),
                'matricula_id' => $matriculaId
            ]);
            throw $e;
        }
    }

    private function validateAndGetMatricula($matriculaId) {
        $sql = "SELECT m.*, t.nombre as taller_nombre, t.costo, 
                       e.id as estudiante_id, e.nombre as estudiante_nombre
                FROM matriculas m 
                JOIN talleres t ON m.taller_id = t.id 
                JOIN estudiantes e ON m.estudiante_id = e.id 
                WHERE m.id = ? AND m.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matriculaId]);
        $matricula = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$matricula) {
            throw new Exception('Matrícula no encontrada');
        }

        if ($matricula['estado'] === 'activo') {
            throw new Exception('La matrícula ya está pagada');
        }

        return $matricula;
    }

    private function calculateAmount($matricula) {
        $monto = $matricula['costo'];

        // Aplicar descuentos si existen
        $descuento = $this->getDescuentoAplicable($matricula);
        if ($descuento) {
            $monto = $monto * (1 - $descuento['porcentaje'] / 100);
        }

        return round($monto, 2);
    }

    private function getDescuentoAplicable($matricula) {
        $sql = "SELECT d.* FROM descuentos d
                WHERE d.activo = 1 
                AND CURRENT_TIMESTAMP BETWEEN d.fecha_inicio AND d.fecha_fin
                AND (d.taller_id IS NULL OR d.taller_id = ?)
                ORDER BY d.porcentaje DESC
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matricula['taller_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generateTransactionId() {
        return sprintf(
            'TRX-%s-%s',
            date('Ymd'),
            substr(uniqid(), -8)
        );
    }

    private function registerPaymentAttempt($data) {
        $sql = "INSERT INTO intentos_pago (
                    matricula_id,
                    transaction_id,
                    monto,
                    metodo,
                    estado,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['matricula_id'],
            $data['transaction_id'],
            $data['amount'],
            'yape',
            'iniciado'
        ]);

        return $this->db->lastInsertId();
    }
}