<?php
/**
 * Servicio de Email
 * Ruta: includes/api/EmailService.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $logger;
    private $config;
    private static $instance = null;

    private function __construct() {
        $this->config = require __DIR__ . '/../../Config/services.php';
        $this->logger = ActivityLogger::getInstance();
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
        $this->mailer->Host = $this->config['email']['smtp_host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['email']['smtp_user'];
        $this->mailer->Password = $this->config['email']['smtp_pass'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['email']['smtp_port'];
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(
            $this->config['email']['from_address'],
            $this->config['email']['from_name']
        );
    }

    public function send($to, $subject, $template, $data = [], $attachments = []) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->renderTemplate($template, $data);

            foreach ($attachments as $attachment) {
                if (isset($attachment['path'])) {
                    $this->mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }

            $sent = $this->mailer->send();
            $this->logger->info('Email enviado', [
                'to' => $to,
                'subject' => $subject
            ]);

            return [
                'success' => true,
                'message_id' => $this->mailer->getLastMessageID()
            ];
        } catch (Exception $e) {
            $this->logger->error('Error enviando email', [
                'error' => $e->getMessage(),
                'to' => $to
            ]);
            throw $e;
        }
    }

    private function renderTemplate($template, $data) {
        ob_start();
        extract($data);
        include __DIR__ . '/../../views/emails/' . $template . '.php';
        return ob_get_clean();
    }

    public function sendWelcome($to, $userData) {
        return $this->send(
            $to,
            'Bienvenido a Talleres de Verano',
            'welcome',
            $userData
        );
    }

    public function sendPaymentConfirmation($to, $paymentData) {
        return $this->send(
            $to,
            'Confirmación de Pago',
            'payment_confirmation',
            $paymentData
        );
    }

    public function sendPasswordReset($to, $resetData) {
        return $this->send(
            $to,
            'Recuperación de Contraseña',
            'password_reset',
            $resetData
        );
    }

    public function sendEnrollmentConfirmation($to, $enrollmentData) {
        $pdfService = new PDFService();
        $pdf = $pdfService->generateEnrollmentPDF($enrollmentData);

        return $this->send(
            $to,
            'Confirmación de Matrícula',
            'enrollment_confirmation',
            $enrollmentData,
            [
                [
                    'path' => $pdf['path'],
                    'name' => 'matricula.pdf'
                ]
            ]
        );
    }
}