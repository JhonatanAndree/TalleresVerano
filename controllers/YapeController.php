<?php
class YapeController {
    private $model;
    private $logger;
    private $security;
    private $config;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new YapeModel($db);
        $this->logger = ActivityLogger::getInstance($db);
        $this->security = SecurityHelper::getInstance();
        $this->config = require_once __DIR__ . '/../Config/yape.php';
    }

    public function initializePago() {
        try {
            if (!$this->security->validateCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token de seguridad inválido');
            }

            $data = $this->validatePagoData($_POST);
            $transactionId = $this->generateTransactionId();

            // Crear registro temporal de pago
            $pagoId = $this->model->createPendingPago([
                'estudiante_id' => $data['estudiante_id'],
                'taller_id' => $data['taller_id'],
                'monto' => $data['monto'],
                'transaction_id' => $transactionId,
                'estado' => 'pendiente'
            ]);

            // Iniciar transacción con Yape
            $yapeResponse = $this->initializeYapeTransaction($data['monto'], $transactionId);

            if ($yapeResponse['success']) {
                $this->logger->info('Pago Yape iniciado', [
                    'pago_id' => $pagoId,
                    'transaction_id' => $transactionId
                ]);

                return json_encode([
                    'success' => true,
                    'qr_data' => $yapeResponse['qr_data'],
                    'transaction_id' => $transactionId
                ]);
            } else {
                throw new Exception('Error al iniciar transacción con Yape');
            }
        } catch (Exception $e) {
            $this->logger->error('Error iniciando pago Yape', ['error' => $e->getMessage()]);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function verifyPayment() {
        try {
            $transactionId = filter_var($_POST['transaction_id'], FILTER_SANITIZE_STRING);
            
            // Verificar estado en Yape
            $yapeStatus = $this->checkYapeStatus($transactionId);

            if ($yapeStatus['success'] && $yapeStatus['status'] === 'completed') {
                // Actualizar estado de pago
                $this->model->updatePagoStatus($transactionId, 'completado', $yapeStatus['details']);
                
                // Registrar en el historial
                $this->logger->info('Pago Yape completado', ['transaction_id' => $transactionId]);
                
                return json_encode(['success' => true, 'status' => 'completed']);
            }

            return json_encode(['success' => true, 'status' => $yapeStatus['status']]);
        } catch (Exception $e) {
            $this->logger->error('Error verificando pago Yape', ['error' => $e->getMessage()]);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    private function validatePagoData($data) {
        return [
            'estudiante_id' => filter_var($data['estudiante_id'], FILTER_SANITIZE_NUMBER_INT),
            'taller_id' => filter_var($data['taller_id'], FILTER_SANITIZE_NUMBER_INT),
            'monto' => filter_var($data['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
        ];
    }

    private function generateTransactionId() {
        return uniqid('YAPE-', true);
    }

    private function initializeYapeTransaction($monto, $transactionId) {
        // Configuración de Yape
        $yapeEndpoint = $this->config['api_endpoint'] . '/initTransaction';
        $headers = [
            'Authorization: Bearer ' . $this->config['api_key'],
            'Content-Type: application/json'
        ];

        // Datos de la transacción
        $data = [
            'amount' => $monto,
            'currency' => 'PEN',
            'transaction_id' => $transactionId,
            'callback_url' => $this->config['callback_url']
        ];

        // Realizar petición a Yape
        $ch = curl_init($yapeEndpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Error en la comunicación con Yape');
        }

        return json_decode($response, true);
    }

    private function checkYapeStatus($transactionId) {
        $yapeEndpoint = $this->config['api_endpoint'] . '/checkStatus/' . $transactionId;
        $headers = [
            'Authorization: Bearer ' . $this->config['api_key']
        ];

        $ch = curl_init($yapeEndpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Error verificando estado en Yape');
        }

        return json_decode($response, true);
    }
}