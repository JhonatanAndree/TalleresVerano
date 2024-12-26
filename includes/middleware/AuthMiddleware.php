<?php
/**
 * Middleware de autenticación
 * Ruta: includes/middleware/AuthMiddleware.php
 */

class AuthMiddleware {
    private $session;
    private $security;
    private $logger;
    private $config;

    public function __construct() {
        $this->session = Session::getInstance();
        $this->security = SecurityHelper::getInstance();
        $this->logger = ActivityLogger::getInstance();
        $this->config = require __DIR__ . '/../../Config/security.php';
    }

    public function handle() {
        // Verificar sesión activa
        if (!$this->session->isAuthenticated()) {
            $this->logger->warning('Intento de acceso sin autenticación');
            return $this->redirectToLogin();
        }

        // Validar timeout de sesión
        if ($this->isSessionExpired()) {
            $this->logger->info('Sesión expirada', ['user_id' => $this->session->get('user.id')]);
            $this->session->destroy();
            return $this->redirectToLogin('Sesión expirada');
        }

        // Validar IP
        if (!$this->validateIP()) {
            $this->logger->warning('Cambio de IP detectado', [
                'user_id' => $this->session->get('user.id'),
                'old_ip' => $this->session->get('user.ip'),
                'new_ip' => $_SERVER['REMOTE_ADDR']
            ]);
            $this->session->destroy();
            return $this->redirectToLogin('Sesión invalidada por seguridad');
        }

        // Regenerar ID de sesión periódicamente
        if ($this->shouldRegenerateSession()) {
            $this->session->regenerate();
            $this->session->set('last_regeneration', time());
        }

        // Actualizar última actividad
        $this->session->set('last_activity', time());
        
        return true;
    }

    private function isSessionExpired() {
        $lastActivity = $this->session->get('last_activity');
        return ($lastActivity && (time() - $lastActivity) > $this->config['session']['lifetime']);
    }

    private function validateIP() {
        $sessionIP = $this->session->get('user.ip');
        return !$sessionIP || $sessionIP === $_SERVER['REMOTE_ADDR'];
    }

    private function shouldRegenerateSession() {
        $lastRegeneration = $this->session->get('last_regeneration', 0);
        return (time() - $lastRegeneration) > 300; // 5 minutos
    }

    private function redirectToLogin($message = null) {
        if ($message) {
            $this->session->flash('error', $message);
        }
        header('Location: /login');
        exit;
    }
}