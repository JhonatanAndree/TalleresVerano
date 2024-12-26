<?php
/**
 * Servicio de integración con Yape
 * Ruta: includes/payment/YapeService.php
 */

class YapeService {
    private $apiKey;
    private $merchantId;
    private $apiUrl;
    private $logger;
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../Config/services.php';
        $this->apiKey = $config['yape']['api_key'];
        $this->merchantId = $config['yape']['merchant_id'];
        $this->apiUrl = $config['yape']['api_url'];
        $this->logger = ActivityLogger::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }

    public function initializePayment($data) {
        try {
            $this->validatePaymentData($data);
            
            $payload = [
                'merchant_id' => $this->merchantId,
                'amount' => $data['amount'],
                'currency' => 'PEN',
                'transaction_id' => $data['transaction_id'],
                'notification_url' => getenv('APP_URL') . '/webhook/yape',
                'expiration' => date('Y-m-d\TH:i:s\Z', strtotime('+15 minutes'))
            ];

            $response = $this->makeRequest('/payments', 'POST', $payload);
            
            if ($response['success']) {
                $this->registerPaymentIntent($data, $response);
            }

            return $response;
        } catch (Exception $e) {
            $this->logger->error('Error iniciando pago Yape', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function verifyPayment($transactionId) {
        try {
            $response = $this->makeRequest("/payments/$transactionId", 'GET');
            
            if ($response['status'] === 'completed') {
                $this->confirmPayment($transactionId, $response);
            }

            return $response;
        } catch (Exception $e) {
            $this->logger->error('Error verificando pago', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            throw $e;
        }
    }

    public function handleWebhook($payload) {
        try {
            if (!$this->validateWebhookSignature($payload)) {
                throw new Exception('Firma webhook inválida');
            }

            $transactionId = $payload['transaction_id'];
            $status = $payload['status'];

            switch ($status) {
                case 'completed':
                    $this->confirmPayment($transactionId, $payload);
                    break;
                    
                case 'expired':
                    $this->expirePayment($transactionId);
                    break;
                    
                case 'failed':
                    $this->failPayment($transactionId, $payload['error']);
                    break;
            }

            return true;
        } catch (Exception $e) {
            $this->logger->error('Error procesando webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            throw $e;
        }
    }

    private function validatePaymentData($data) {
        $requiredFields = ['amount', 'transaction_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }

        if ($data['amount'] <= 0) {
            throw new Exception('Monto debe ser mayor a 0');
        }
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $this->apiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ]
        ];

        if ($method !== 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception("Error en petición Yape: $error");
        }

        return json_decode($response, true);
    }

    private function validateWebhookSignature($payload) {
        $signature = $_SERVER['HTTP_X_YAPE_SIGNATURE'] ?? '';
        $expectedSignature = hash_hmac(
            'sha256',
            json_encode($payload),
            $this->apiKey
        );

        return hash_equals($expectedSignature, $signature);
    }

    private function registerPaymentIntent($data, $response) {
        $sql = "INSERT INTO pagos_yape (
                    transaction_id, 
                    amount,
                    qr_data,
                    expiration,
                    status
                ) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['transaction_id'],
            $data['amount'],
            $response['qr_data'],
            $response['expiration'],
            'pending'
        ]);
    }

    private function confirmPayment($transactionId, $response) {
        try {
            $this->db->beginTransaction();

            // Actualizar pago Yape
            $sql = "UPDATE pagos_yape 
                    SET status = 'completed',
                        payment_id = ?,
                        completed_at = CURRENT_TIMESTAMP,
                        response_data = ?
                    WHERE transaction_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $response['payment_id'],
                json_encode($response),
                $transactionId
            ]);

            // Activar matrícula asociada
            $sql = "UPDATE matriculas m
                    JOIN pagos p ON m.id = p.matricula_id
                    SET m.estado = 'activo'
                    WHERE p.transaction_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$transactionId]);

            $this->db->commit();

            // Notificar al usuario
            $notificationService = new NotificationService();
            $notificationService->sendPaymentConfirmation($transactionId);

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function expirePayment($transactionId) {
        $sql = "UPDATE pagos_yape 
                SET status = 'expired',
                    updated_at = CURRENT_TIMESTAMP
                WHERE transaction_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transactionId]);
    }

    private function failPayment($transactionId, $error) {
        $sql = "UPDATE pagos_yape 
                SET status = 'failed',
                    error_message = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE transaction_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$error, $transactionId]);
    }
}