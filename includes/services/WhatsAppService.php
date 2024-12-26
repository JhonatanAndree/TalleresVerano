<?php
class WhatsAppService {
    private $apiKey;
    private $apiUrl;
    private $logger;
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../Config/services.php';
        $this->apiKey = $config['whatsapp']['api_key'];
        $this->apiUrl = $config['whatsapp']['api_url'];
        $this->logger = ActivityLogger::getInstance();
        $this->db = require __DIR__ . '/../../Config/db.php';
    }

    public function enviarMensaje($numero, $mensaje, $template = null, $variables = []) {
        try {
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatearNumero($numero),
                'type' => $template ? 'template' : 'text'
            ];

            if ($template) {
                $data['template'] = [
                    'name' => $template,
                    'language' => ['code' => 'es'],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => $this->formatearVariables($variables)
                        ]
                    ]
                ];
            } else {
                $data['text'] = ['body' => $mensaje];
            }

            $response = $this->hacerPeticion('/messages', 'POST', $data);
            $messageId = $response['messages'][0]['id'];

            $this->registrarMensaje($numero, $mensaje, $messageId, $template);
            return ['success' => true, 'message_id' => $messageId];
        } catch (Exception $e) {
            $this->logger->error('Error enviando WhatsApp', [
                'error' => $e->getMessage(),
                'numero' => $numero
            ]);
            throw $e;
        }
    }

    public function verificarEstado($messageId) {
        try {
            $response = $this->hacerPeticion("/messages/{$messageId}", 'GET');
            $this->actualizarEstadoMensaje($messageId, $response['status']);
            return $response;
        } catch (Exception $e) {
            $this->logger->error('Error verificando estado', [
                'error' => $e->getMessage(),
                'message_id' => $messageId
            ]);
            throw $e;
        }
    }

    public function manejarWebhook($payload) {
        try {
            $entry = $payload['entry'][0];
            $changes = $entry['changes'][0];
            $value = $changes['value'];

            if (isset($value['messages'])) {
                foreach ($value['messages'] as $message) {
                    $this->procesarMensajeEntrante($message);
                }
            }

            if (isset($value['statuses'])) {
                foreach ($value['statuses'] as $status) {
                    $this->actualizarEstadoMensaje($status['id'], $status['status']);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logger->error('Error en webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    private function procesarMensajeEntrante($message) {
        $numero = $message['from'];
        $texto = $message['text']['body'] ?? '';
        $messageId = $message['id'];

        $sql = "INSERT INTO whatsapp_mensajes_entrantes 
                (numero, mensaje, message_id, created_at) 
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero, $texto, $messageId]);

        // Procesar comandos o palabras clave
        if (stripos($texto, 'consulta') !== false) {
            $this->procesarConsulta($numero, $texto);
        }
    }

    private function procesarConsulta($numero, $texto) {
        // Extraer DNI del mensaje
        if (preg_match('/\b\d{8}\b/', $texto, $matches)) {
            $dni = $matches[0];
            
            // Buscar estudiante
            $sql = "SELECT e.*, t.nombre as taller_nombre 
                   FROM estudiantes e 
                   JOIN matriculas m ON e.id = m.estudiante_id 
                   JOIN talleres t ON m.taller_id = t.id 
                   WHERE e.dni = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dni]);
            $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($estudiante) {
                $respuesta = "Información del estudiante:\n" .
                            "Nombre: {$estudiante['nombre']} {$estudiante['apellido']}\n" .
                            "Taller: {$estudiante['taller_nombre']}";
                
                $this->enviarMensaje($numero, $respuesta);
            } else {
                $this->enviarMensaje($numero, "No se encontró información para el DNI proporcionado.");
            }
        }
    }

    private function registrarMensaje($numero, $mensaje, $messageId, $template = null) {
        $sql = "INSERT INTO whatsapp_mensajes 
                (numero, mensaje, message_id, template, estado, created_at) 
                VALUES (?, ?, ?, ?, 'enviado', CURRENT_TIMESTAMP)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero, $mensaje, $messageId, $template]);
    }

    private function actualizarEstadoMensaje($messageId, $estado) {
        $sql = "UPDATE whatsapp_mensajes 
                SET estado = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE message_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estado, $messageId]);
    }

    private function formatearNumero($numero) {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        return strlen($numero) === 9 ? '51' . $numero : $numero;
    }

    private function formatearVariables($variables) {
        return array_map(function($valor) {
            return ['type' => 'text', 'text' => $valor];
        }, array_values($variables));
    }

    private function hacerPeticion($endpoint, $metodo, $data = null) {
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
            CURLOPT_CUSTOMREQUEST => $metodo,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if ($data && in_array($metodo, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($err) {
            throw new Exception("Error en petición WhatsApp: $err");
        }

        if ($statusCode >= 400) {
            throw new Exception("Error en WhatsApp API: $response");
        }

        return json_decode($response, true);
    }
}