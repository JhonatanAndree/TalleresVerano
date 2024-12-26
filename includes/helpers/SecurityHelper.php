<?php
/**
 * Helper de seguridad del sistema
 * @package Helpers
 */

class SecurityHelper {
    private static $config;
    private static $instance = null;

    private function __construct() {
        self::$config = require_once __DIR__ . '/../../Config/security.php';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Genera un token CSRF
     */
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(self::$config['csrf']['token_length'] / 2));
        $_SESSION['csrf_token'] = [
            'token' => $token,
            'expires' => time() + self::$config['csrf']['token_lifetime']
        ];
        return $token;
    }

    /**
     * Valida un token CSRF
     */
    public function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        if (time() > $_SESSION['csrf_token']['expires']) {
            unset($_SESSION['csrf_token']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token']['token'], $token);
    }

    /**
     * Encripta datos sensibles
     */
    public function encrypt($data) {
        $method = self::$config['encryption']['method'];
        $key = base64_decode(self::$config['encryption']['key']);
        $iv = random_bytes(self::$config['encryption']['iv_length']);
        
        $ciphertext = openssl_encrypt(
            $data,
            $method,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Desencripta datos
     */
    public function decrypt($data) {
        $method = self::$config['encryption']['method'];
        $key = base64_decode(self::$config['encryption']['key']);
        $data = base64_decode($data);
        
        $iv = substr($data, 0, self::$config['encryption']['iv_length']);
        $tag = substr($data, self::$config['encryption']['iv_length'], 16);
        $ciphertext = substr($data, self::$config['encryption']['iv_length'] + 16);

        return openssl_decrypt(
            $ciphertext,
            $method,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    }

    /**
     * Valida fortaleza de contraseña
     */
    public function validatePassword($password) {
        $config = self::$config['password'];
        
        if (strlen($password) < $config['min_length']) return false;
        if ($config['require_number'] && !preg_match('/\d/', $password)) return false;
        if ($config['require_special'] && !preg_match('/[^A-Za-z0-9]/', $password)) return false;
        if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) return false;
        if ($config['require_lowercase'] && !preg_match('/[a-z]/', $password)) return false;
        
        return true;
    }

    /**
     * Hashea contraseña
     */
    public function hashPassword($password) {
        return password_hash(
            $password,
            self::$config['password']['hash_algo'],
            self::$config['password']['hash_options']
        );
    }

    /**
     * Verifica contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Aplica headers de seguridad
     */
    public function setSecurityHeaders() {
        foreach (self::$config['headers'] as $header => $value) {
            header("$header: $value");
        }
    }

    /**
     * Verifica límites de intentos
     */
    public function checkRateLimit($type, $identifier) {
        if (!isset($_SESSION['rate_limits'][$type][$identifier])) {
            $_SESSION['rate_limits'][$type][$identifier] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $limit = self::$config['rate_limits'][$type];
        $data = &$_SESSION['rate_limits'][$type][$identifier];

        if (time() - $data['first_attempt'] > $limit['decay_minutes'] * 60) {
            $data['attempts'] = 0;
            $data['first_attempt'] = time();
        }

        $data['attempts']++;
        return $data['attempts'] <= $limit['attempts'];
    }

    /**
     * Genera token JWT
     */
    public function generateJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$config['tokens']['jwt_algo']]);
        $payload['exp'] = time() + self::$config['tokens']['jwt_lifetime'];
        $payload = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            self::$config['tokens']['jwt_secret'],
            true
        );
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}