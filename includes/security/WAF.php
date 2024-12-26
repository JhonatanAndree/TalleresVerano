<?php
class WAF {
    private $config;
    private $logger;
    private static $instance = null;

    private function __construct() {
        $this->config = require __DIR__ . '/../../Config/security.php';
        $this->logger = ActivityLogger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function validateRequest() {
        if (!$this->config['waf']['enabled']) {
            return true;
        }

        $this->validateIP();
        $this->validateRateLimit();
        $this->validateInput();
        $this->validateHeaders();
        
        return true;
    }

    private function validateIP() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (in_array($ip, $this->config['waf']['ip_whitelist'])) {
            return true;
        }

        // Verificar lista negra
        if ($this->isBlacklisted($ip)) {
            $this->logger->warning('IP bloqueada intentando acceder', ['ip' => $ip]);
            $this->block('IP no autorizada');
        }
    }

    private function validateRateLimit() {
        if (!$this->config['waf']['rate_limit']['enabled']) {
            return true;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:$ip";
        
        $requests = apcu_fetch($key) ?: 0;
        if ($requests >= $this->config['waf']['rate_limit']['max_requests']) {
            $this->logger->warning('Rate limit excedido', ['ip' => $ip]);
            $this->block('Demasiadas solicitudes');
        }

        apcu_inc($key, 1, $success, $this->config['waf']['rate_limit']['time_window']);
    }

    private function validateInput() {
        foreach ($_REQUEST as $key => $value) {
            if ($this->config['waf']['rules']['sql_injection'] && $this->detectSQLInjection($value)) {
                $this->block('SQL Injection detectada');
            }
            if ($this->config['waf']['rules']['xss'] && $this->detectXSS($value)) {
                $this->block('XSS detectado');
            }
            if ($this->config['waf']['rules']['rfi'] && $this->detectRFI($value)) {
                $this->block('RFI detectado');
            }
            if ($this->config['waf']['rules']['path_traversal'] && $this->detectPathTraversal($value)) {
                $this->block('Path Traversal detectado');
            }
        }
    }

    private function validateHeaders() {
        $headers = getallheaders();
        foreach ($headers as $header => $value) {
            if ($this->detectMaliciousHeader($header, $value)) {
                $this->block('Header malicioso detectado');
            }
        }
    }

    private function detectSQLInjection($value) {
        $patterns = [
            "/(\%27)|(\')|(\-\-)|(%23)|(#)/i",
            "/(\%27)|(\')|(\-\-)|(\%3B)|(;)/i",
            "/(union.*select|update.*set|insert.*into)/i"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }

    private function detectXSS($value) {
        return preg_match('/(script.*?>.*?<\/script>)|(<.*?javascript:)|(onclick|onload|onmouseover)/i', $value);
    }

    private function detectRFI($value) {
        return preg_match('/((http|https|ftp|php|data):\/\/)/i', $value);
    }

    private function detectPathTraversal($value) {
        return preg_match('/\.\.\/|\.\.\\\/i', $value);
    }

    private function detectMaliciousHeader($header, $value) {
        $patterns = [
            '/(<|%3C).*script.*(>|%3E)/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/data:/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }

    private function block($reason) {
        $this->logger->error('Ataque detectado', [
            'reason' => $reason,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        header('HTTP/1.1 403 Forbidden');
        exit('Acceso Denegado');
    }

    private function isBlacklisted($ip) {
        // Implementar lógica de lista negra
        return false;
    }
}