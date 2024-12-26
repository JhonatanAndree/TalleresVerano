<?php
class NotificationService {
    private $db;
    private $logger;
    private $whatsappService;
    private $mailer;

    public function __construct() {
        $this->db = require __DIR__ . '/../../Config/db.php';
        $this->logger = ActivityLogger::getInstance();
        $this->whatsappService = new WhatsAppService();
        $this->mailer = new MailerHelper();
    }

    public function notificarPagoCompletado($pagoId) {
        $pago = $this->obtenerDatosPago($pagoId);
        
        $this->crearNotificacionSistema($pago);
        $this->enviarNotificacionWhatsApp($pago);
        $this->enviarEmailConfirmacion($pago);
    }

    private function obtenerDatosPago($pagoId) {
        $sql = "SELECT p.*, 
                       e.nombre as estudiante_nombre,
                       e.apellido as estudiante_apellido,
                       t.nombre as taller_nombre,
                       u.email,
                       u.telefono
                FROM pagos p
                JOIN estudiantes e ON p.estudiante_id = e.id
                JOIN talleres t ON p.taller_id = t.id
                JOIN usuarios u ON e.padre_id = u.id
                WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pagoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function crearNotificacionSistema($pago) {
        $sql = "INSERT INTO notificaciones (
                    usuario_id,
                    tipo,
                    titulo,
                    mensaje,
                    estado,
                    data
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $pago['padre_id'],
            'pago_completado',
            'Pago Confirmado',
            "Se ha confirmado el pago del taller {$pago['taller_nombre']}",
            'pendiente',
            json_encode($pago)
        ]);
    }

    private function enviarNotificacionWhatsApp($pago) {
        if ($pago['telefono']) {
            $mensaje = "¡Pago Confirmado!\n\n" .
                      "Taller: {$pago['taller_nombre']}\n" .
                      "Estudiante: {$pago['estudiante_nombre']} {$pago['estudiante_apellido']}\n" .
                      "Monto: S/. {$pago['monto']}\n\n" .
                      "Gracias por su pago.";

            try {
                $this->whatsappService->enviarMensaje($pago['telefono'], $mensaje);
            } catch (Exception $e) {
                $this->logger->error('Error enviando WhatsApp', [
                    'error' => $e->getMessage(),
                    'pago_id' => $pago['id']
                ]);
            }
        }
    }

    private function enviarEmailConfirmacion($pago) {
        if ($pago['email']) {
            try {
                $this->mailer->enviarCorreo(
                    $pago['email'],
                    'Confirmación de Pago - Talleres de Verano',
                    'emails/confirmacion_pago',
                    $pago
                );
            } catch (Exception $e) {
                $this->logger->error('Error enviando email', [
                    'error' => $e->getMessage(),
                    'pago_id' => $pago['id']
                ]);
            }
        }
    }
}