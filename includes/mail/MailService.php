<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private $mailer;
    
    public function __construct() {
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
        $this->mailer->CharSet = 'UTF-8';
    }

    public function enviarCorreoRecuperacion($email, $token) {
        try {
            $template = $this->obtenerTemplate('recuperacion', [
                'token' => $token,
                'url' => BASE_URL . "/reset-password.php?token=" . $token
            ]);

            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Recuperación de Contraseña - Talleres de Verano';
            $this->mailer->Body = $template;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            createLog(0, 'mail', "Error envío correo recuperación: {$e->getMessage()}");
            return false;
        }
    }

    public function enviarConfirmacionMatricula($email, $datos) {
        try {
            $template = $this->obtenerTemplate('confirmacion_matricula', $datos);

            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Confirmación de Matrícula - Talleres de Verano';
            $this->mailer->Body = $template;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            createLog(0, 'mail', "Error envío confirmación matrícula: {$e->getMessage()}");
            return false;
        }
    }

    private function obtenerTemplate($nombre, $datos) {
        $template = file_get_contents(MAIL_TEMPLATES[$nombre]);
        foreach ($datos as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
}