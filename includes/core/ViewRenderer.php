<?php
/**
 * Sistema de renderizado de vistas
 * Ruta: includes/core/ViewRenderer.php
 */

class ViewRenderer {
    private $basePath;
    private $cache;
    private $logger;
    private static $instance = null;

    private function __construct() {
        $this->basePath = __DIR__ . '/../../views';
        $this->cache = CacheManager::getInstance();
        $this->logger = ActivityLogger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($view, $data = [], $useCache = true) {
        try {
            $viewPath = $this->resolveViewPath($view);
            $cacheKey = $this->getCacheKey($view, $data);

            if ($useCache && $cached = $this->cache->get($cacheKey)) {
                return $cached;
            }

            $content = $this->renderView($viewPath, $data);

            if ($useCache) {
                $this->cache->set($cacheKey, $content, 3600);
            }

            return $content;
        } catch (Exception $e) {
            $this->logger->error('Error renderizando vista', [
                'view' => $view,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function resolveViewPath($view) {
        $viewPath = $this->basePath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Vista no encontrada: $view");
        }

        return $viewPath;
    }

    private function renderView($viewPath, $data) {
        ob_start();
        extract($data);
        include $viewPath;
        return ob_get_clean();
    }

    private function getCacheKey($view, $data) {
        return 'view_' . md5($view . serialize($data));
    }

    public function partial($view, $data = []) {
        return $this->render($view, $data, false);
    }

    public function exists($view) {
        return file_exists($this->resolveViewPath($view));
    }

    public function share($key, $value) {
        $this->shared[$key] = $value;
        return $this;
    }

    public function clearCache() {
        return $this->cache->clear('view_*');
    }
}