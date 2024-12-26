<?php
/**
 * API de WhatsApp
 * Ruta: includes/api/WhatsAppAPI.php
 */

class WhatsAppAPI {
    private $apiKey;
    private $apiUrl;
    private $logger;
    private static $instance = null;

    private function __construct() {
        $config = require __DIR__ . '/../../Config/services.php';
        $this->apiKey = $config['whatsapp']['api_key'];
        $this->apiUrl = $config['whatsapp']['api_url'];
        $this->logger = ActivityLogger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function sendMessage($number, $message, $template = null, $mediaUrl = null) {
        try {
            $endpoint = '/messages';
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatNumber($number),
                'recipient_type' => 'individual'
            ];

            if ($template) {
                $data['type'] = 'template';
                $data['template'] = [
                    'name' => $template,
                    'language' => ['code' => 'es'],
                    'components' => []
                ];
            } else {
                $data['type'] = 'text';
                $data['text'] = ['body' => $message];
            }

            if ($mediaUrl) {
                $data['type'] = 'media';
                $data['media'] = ['url' => $mediaUrl];
            }

            $response = $this->makeRequest($endpoint, 'POST', $data);
            $messageId = $response['messages'][0]['id'] ?? null;

            $this->logger->info('Mensaje WhatsApp enviado', [
                'number' => $number,
                'message_id' => $messageId
            ]);

            return [
                'success' => true,
                'message_id' => $messageId,
                'response' => $response
            ];
        } catch (Exception $e) {
            $this->logger->error('Error enviando mensaje WhatsApp', [
                'error' => $e->getMessage(),
                'number' => $number
            ]);
            throw $e;
        }
    }

    private function formatNumber($number) {
        $number = preg_replace('/[^0-9]/', '', $number);
        return '51' . $number;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $curl = curl_init();
        $url = $this->apiUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ]);

        if ($data && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            throw new Exception('Error en petición WhatsApp: ' . $err);
        }

        if ($statusCode >= 400) {
            throw new Exception('Error en API WhatsApp: ' . $response);
        }

        return json_decode($response, true);
    }

    public function getMessageStatus($messageId) {
        return $this->makeRequest("/messages/$messageId");
    }
}