<?php
/**
 * Helper para envío de correos
 * Ruta: includes/helpers/MailerHelper.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerHelper {
    private $mailer;
    private $config;
    private static $instance = null;

    private function __construct() {
        $this->config = require_once __DIR__ . '/../../Config/mail.php';
        $this->initializeMailer();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['port'];
        $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function sendMail($to, $subject, $body, $attachments = []) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->getEmailTemplate($subject, $body);
            
            foreach ($attachments as $attachment) {
                if (isset($attachment['path'])) {
                    $this->mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error enviando correo: " . $e->getMessage());
            return false;
        }
    }

    public function sendFichaInscripcion($to, $fichaData) {
        $fileHelper = FileHelper::getInstance();
        $pdfResult = $fileHelper->generateFichaInscripcion($fichaData);
        
        if ($pdfResult['success']) {
            return $this->sendMail(
                $to,
                'Ficha de Inscripción - Talleres de Verano',
                $this->getFichaInscripcionMessage($fichaData),
                [['path' => $pdfResult['path'], 'name' => 'ficha_inscripcion.pdf']]
            );
        }
        return false;
    }

    public function sendCredencialesAcceso($to, $userData) {
        return $this->sendMail(
            $to,
            'Credenciales de Acceso - Talleres de Verano',
            $this->getCredencialesMessage($userData)
        );
    }

    private function getEmailTemplate($subject, $body) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .content { margin-bottom: 30px; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>' . htmlspecialchars($subject) . '</h2>
                </div>
                <div class="content">
                    ' . $body . '
                </div>
                <div class="footer">
                    ' . $this->getEmailFooter() . '
                </div>
            </div>
        </body>
        </html>';
    }

    private function getFichaInscripcionMessage($data) {
        return '
        <p>Estimado(a) ' . htmlspecialchars($data['padre']['nombre']) . ',</p>
        <p>Adjunto encontrará la ficha de inscripción para el taller de verano.</p>
        <p>Detalles de la inscripción:</p>
        <ul>
            <li>Estudiante: ' . htmlspecialchars($data['estudiante']['nombre']) . ' ' . htmlspecialchars($data['estudiante']['apellido']) . '</li>
            <li>Taller: ' . htmlspecialchars($data['taller']['nombre']) . '</li>
            <li>Horario: ' . htmlspecialchars($data['taller']['horario']) . '</li>
        </ul>
        <p>Por favor, conserve este documento para futuros trámites.</p>';
    }

    private function getCredencialesMessage($data) {
        return '
        <p>Estimado(a) ' . htmlspecialchars($data['nombre']) . ',</p>
        <p>Sus credenciales de acceso al sistema de Talleres de Verano son:</p>
        <p><strong>Usuario:</strong> ' . htmlspecialchars($data['email']) . '<br>
        <strong>Contraseña:</strong> ' . htmlspecialchars($data['password']) . '</p>
        <p>Por seguridad, le recomendamos cambiar su contraseña después del primer inicio de sesión.</p>';
    }

    private function getEmailFooter() {
        return '
        <p>Municipalidad Distrital de El Porvenir<br>
        Talleres de Verano 2025<br>
        ' . $this->config['contact_phone'] . '<br>
        ' . $this->config['contact_email'] . '</p>';
    }
}