<?php
/**
 * Gestor de APIs
 * Ruta: includes/api/ApiManager.php
 */

class ApiManager {
    private $config;
    private $logger;
    private static $instance = null;

    private function __construct() {
        $this->config = require __DIR__ . '/../../Config/api.php';
        $this->logger = ActivityLogger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function request($service, $endpoint, $method = 'GET', $data = null, $headers = []) {
        $serviceConfig = $this->getServiceConfig($service);
        $url = $serviceConfig['base_url'] . $endpoint;
        
        $defaultHeaders = [
            'Authorization' => $this->getAuthHeader($service),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->formatHeaders(array_merge($defaultHeaders, $headers)),
            CURLOPT_TIMEOUT => $serviceConfig['timeout'] ?? 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->logger->error("API Error: $service", ['error' => $error]);
            throw new Exception("Error en la comunicación con $service: $error");
        }

        return $this->handleResponse($response, $statusCode, $service);
    }

    private function handleResponse($response, $statusCode, $service) {
        $data = json_decode($response, true);

        if ($statusCode >= 400) {
            $error = $data['error'] ?? 'Error desconocido';
            $this->logger->error("API Error: $service", [
                'status' => $statusCode,
                'error' => $error
            ]);
            throw new Exception("Error en $service: $error");
        }

        return $data;
    }

    private function getServiceConfig($service) {
        if (!isset($this->config['services'][$service])) {
            throw new Exception("Servicio no configurado: $service");
        }
        return $this->config['services'][$service];
    }

    private function getAuthHeader($service) {
        $config = $this->getServiceConfig($service);
        
        switch ($config['auth_type']) {
            case 'bearer':
                return 'Bearer ' . $config['api_key'];
            case 'basic':
                return 'Basic ' . base64_encode($config['username'] . ':' . $config['password']);
            default:
                return '';
        }
    }

    private function formatHeaders($headers) {
        return array_map(
            fn($key, $value) => "$key: $value",
            array_keys($headers),
            $headers
        );
    }

    // Métodos específicos para cada servicio
    public function verificarPagoYape($transactionId) {
        return $this->request('yape', "/transactions/$transactionId");
    }

    public function notificarWhatsApp($numero, $mensaje, $template = null) {
        $data = [
            'phone' => $numero,
            'message' => $mensaje
        ];
        if ($template) {
            $data['template'] = $template;
        }
        return $this->request('whatsapp', '/messages', 'POST', $data);
    }

    public function sincronizarDatosGDrive($fileId) {
        return $this->request('gdrive', "/files/$fileId");
    }
}