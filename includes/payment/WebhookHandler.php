<?php
/**
 * Manejador de webhooks para pagos
 * Ruta: includes/payment/WebhookHandler.php
 */

class WebhookHandler {
    private $yapeService;
    private $logger;
    private $db;
    private $security;

    public function __construct() {
        $this->yapeService = new YapeService();
        $this->logger = ActivityLogger::getInstance();
        $this->db = Database::getInstance()->getConnection();
        $this->security = SecurityHelper::getInstance();
    }

    public function handle($payload, $headers = []) {
        try {
            // Validar origen del webhook
            if (!$this->validateSource($headers)) {
                throw new Exception('Origen no autorizado');
            }

            // Validar formato del payload
            $payloadData = $this->validatePayload($payload);

            $this->logger->info('Webhook recibido', [
                'source' => $payloadData['source'],
                'type' => $payloadData['type'],
                'transaction_id' => $payloadData['transaction_id'] ?? null
            ]);

            // Procesar según el tipo de webhook
            switch ($payloadData['source']) {
                case 'yape':
                    return $this->processYapeWebhook($payloadData);
                default:
                    throw new Exception('Fuente de webhook no soportada');
            }
        } catch (Exception $e) {
            $this->logger->error('Error en webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            throw $e;
        }
    }

    private function validateSource($headers) {
        $signature = $headers['X-Webhook-Signature'] ?? '';
        $timestamp = $headers['X-Webhook-Timestamp'] ?? '';
        
        // Verificar que el timestamp no sea muy antiguo (5 minutos)
        if (abs(time() - strtotime($timestamp)) > 300) {
            return false;
        }

        // Verificar firma
        return $this->security->verifyWebhookSignature($signature, $timestamp);
    }

    private function validatePayload($payload) {
        $data = json_decode($payload, true);
        if (!$data) {
            throw new Exception('Payload inválido');
        }

        $requiredFields = ['source', 'type', 'transaction_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Campo requerido faltante: $field");
            }
        }

        return $data;
    }

    private function processYapeWebhook($data) {
        $this->db->beginTransaction();
        try {
            switch ($data['type']) {
                case 'payment.success':
                    $result = $this->yapeService->handlePaymentSuccess($data);
                    break;
                case 'payment.failed':
                    $result = $this->yapeService->handlePaymentFailure($data);
                    break;
                case 'payment.expired':
                    $result = $this->yapeService->handlePaymentExpiration($data);
                    break;
                default:
                    throw new Exception('Tipo de evento no soportado');
            }

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}