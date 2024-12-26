<?php
class MailController {
    private $mailer;

    public function __construct() {
        require_once ROOT_PATH . '/config/mail.php';
        $this->mailer = new PHPMailer(true);
        $this->configurarMailer();
    }

    private function configurarMailer() {
        $this->mailer->isSMTP();
        $this->mailer->Host = MAIL_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = MAIL_USERNAME;
        $this->mailer->Password = MAIL_PASSWORD;
        $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
        $this->mailer->Port = MAIL_PORT;
        $this->mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
    }

    public function enviarCorreoRecuperacion($email, $token) {
        try {
            $template = file_get_contents(MAIL_TEMPLATES['recuperacion']);
            $template = str_replace(
                ['{{token}}', '{{base_url}}'],
                [$token, BASE_URL],
                $template
            );

            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Recuperación de Contraseña';
            $this->mailer->Body = $template;

            return $this->mailer->send();
        } catch (Exception $e) {
            createLog(0, 'mail', "Error envío correo: {$e->getMessage()}");
            return false;
        }
    }
}