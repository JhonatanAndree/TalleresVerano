<?php
/**
 * Métodos de recuperación de contraseña
 * Ruta: Controllers/AuthController.php
 */

public function requestPasswordReset() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->renderPasswordResetForm();
    }

    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!$this->model->userExists($email)) {
            throw new Exception('Email no encontrado');
        }

        $token = $this->security->generateRandomToken();
        $this->model->saveResetToken($email, $token);
        
        $mailer = MailerHelper::getInstance();
        $mailer->sendPasswordResetEmail($email, $token);

        return json_encode([
            'success' => true,
            'message' => 'Se ha enviado un correo con las instrucciones'
        ]);
    } catch (Exception $e) {
        return json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

public function resetPassword($token) {
    if (!$this->model->isValidResetToken($token)) {
        header('Location: /login');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                throw new Exception('Las contraseñas no coinciden');
            }

            if (!$this->security->validatePassword($password)) {
                throw new Exception('La contraseña no cumple con los requisitos mínimos');
            }

            $email = $this->model->getEmailByResetToken($token);
            $this->model->updatePassword($email, $this->security->hashPassword($password));
            $this->model->invalidateResetToken($token);

            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    require_once __DIR__ . '/../views/auth/reset-password.php';
}

private function renderPasswordResetForm() {
    require_once __DIR__ . '/../views/auth/request-reset.php';
}