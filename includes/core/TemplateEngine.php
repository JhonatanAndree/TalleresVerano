<?php
/**
 * Motor de plantillas
 * Ruta: includes/core/TemplateEngine.php
 */

class TemplateEngine {
    private $basePath;
    private $cache;
    private $sections = [];
    private $currentSection;
    private static $instance = null;

    private function __construct() {
        $this->basePath = __DIR__ . '/../../views/layout';
        $this->cache = CacheManager::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function extend($layout) {
        ob_start();
        include $this->basePath . '/' . $layout . '.php';
        return ob_get_clean();
    }

    public function section($name) {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection() {
        if (!$this->currentSection) {
            throw new Exception('No hay sección activa');
        }

        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    public function yield($section) {
        echo $this->sections[$section] ?? '';
    }

    public function include($view, $data = []) {
        extract($data);
        include $this->basePath . '/' . $view . '.php';
    }

    public function escape($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function raw($value) {
        return $value;
    }

    public function clearSections() {
        $this->sections = [];
        $this->currentSection = null;
    }
}