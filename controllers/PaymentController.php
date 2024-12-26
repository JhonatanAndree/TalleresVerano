<?php
/**
 * Controlador de pagos
 * Ruta: Controllers/PaymentController.php
 */

class PaymentController extends Controller {
    private $paymentProcessor;
    private $validator;
    private $cache;

    public function __construct() {
        parent::__construct();
        $this->paymentProcessor = new PaymentProcessor();
        $this->validator = new Validator();
        $this->cache = CacheManager::getInstance();
    }

    public function showPaymentForm($matriculaId) {
        $this->authorize('pagos.iniciar');

        try {
            $matricula = $this->getMatriculaDetails($matriculaId);
            return $this->view('payment/yape', [
                'matricula' => $matricula,
                'estudiante' => $matricula['estudiante'],
                'taller' => $matricula['taller'],
                'monto' => $matricula['monto']
            ]);
        } catch (Exception $e) {
            $this->session->flash('error', $e->getMessage());
            return $this->redirect('/matriculas');
        }
    }

    public function initiate() {
        $this->authorize('pagos.iniciar');

        try {
            $validatedData = $this->validateRequest([
                'matricula_id' => 'required|numeric',
                'payment_method' => 'required|in:yape'
            ]);

            // Prevenir pagos duplicados
            $cacheKey = "payment_attempt_{$validatedData['matricula_id']}";
            if ($this->cache->has($cacheKey)) {
                throw new Exception('Ya existe un pago en proceso');
            }
            $this->cache->set($cacheKey, true, 300);

            $response = $this->paymentProcessor->processPayment(
                $validatedData['matricula_id'],
                $validatedData['payment_method']
            );

            return $this->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'errors' => $e->getErrors()
            ], 422);
        } catch (Exception $e) {
            $this->logger->error('Error iniciando pago', [
                'error' => $e->getMessage(),
                'data' => $_POST
            ]);
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function webhook() {
        try {
            if (!$this->validateWebhookRequest()) {
                throw new Exception('Invalid webhook request');
            }

            $handler = new WebhookHandler();
            $payload = file_get_contents('php://input');
            
            $result = $handler->handle($payload, getallheaders());
            
            return $this->json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->logger->error('Error en webhook', [
                'error' => $e->getMessage(),
                'headers' => getallheaders(),
                'payload' => file_get_contents('php://input')
            ]);
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyStatus($transactionId) {
        try {
            $this->authorize('pagos.verificar');
            
            $status = $this->paymentProcessor->checkStatus($transactionId);
            
            if ($status['status'] === 'completed') {
                $this->cache->delete("payment_attempt_{$status['matricula_id']}");
            }
            
            return $this->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function validateWebhookRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        return $method === 'POST' && 
               strpos($contentType, 'application/json') !== false;
    }

    private function getMatriculaDetails($matriculaId) {
        $matricula = MatriculaModel::findWithRelations($matriculaId);
        if (!$matricula) {
            throw new Exception('Matrícula no encontrada');
        }
        return $matricula;
    }
}