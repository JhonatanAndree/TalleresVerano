<?php
/**
 * Gestor de Assets para producción
 * Ruta: includes/core/AssetManager.php
 */

class AssetManager {
    private $publicPath;
    private $manifestPath;
    private static $instance = null;

    private function __construct() {
        $this->publicPath = __DIR__ . '/../../public';
        $this->manifestPath = $this->publicPath . '/mix-manifest.json';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function optimize() {
        $this->minifyCSS();
        $this->minifyJS();
        $this->optimizeImages();
        $this->generateManifest();
        return true;
    }

    private function minifyCSS() {
        $files = glob($this->publicPath . '/css/*.css');
        foreach ($files as $file) {
            if (strpos($file, '.min.css') !== false) continue;

            $content = file_get_contents($file);
            // Eliminar comentarios
            $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
            // Eliminar espacios
            $content = str_replace(["\r\n", "\r", "\n", "\t"], '', $content);
            $content = preg_replace('/\s+/', ' ', $content);

            $minFile = str_replace('.css', '.min.css', $file);
            file_put_contents($minFile, $content);
        }
    }

    private function minifyJS() {
        $files = glob($this->publicPath . '/js/**/*.js');
        foreach ($files as $file) {
            if (strpos($file, '.min.js') !== false) continue;

            // Usar terser para minificar JS
            $minFile = str_replace('.js', '.min.js', $file);
            exec("terser {$file} -o {$minFile} --compress --mangle");
        }
    }

    private function optimizeImages() {
        $images = array_merge(
            glob($this->publicPath . '/img/*.{jpg,jpeg,png,gif}', GLOB_BRACE),
            glob($this->publicPath . '/uploads/*.{jpg,jpeg,png,gif}', GLOB_BRACE)
        );

        foreach ($images as $image) {
            $info = getimagesize($image);
            if (!$info) continue;

            switch ($info['mime']) {
                case 'image/jpeg':
                    $this->optimizeJPEG($image);
                    break;
                case 'image/png':
                    $this->optimizePNG($image);
                    break;
                case 'image/gif':
                    $this->optimizeGIF($image);
                    break;
            }
        }
    }

    private function optimizeJPEG($file) {
        $image = imagecreatefromjpeg($file);
        imagejpeg($image, $file, 85); // Calidad 85%
        imagedestroy($image);
    }

    private function optimizePNG($file) {
        $image = imagecreatefrompng($file);
        imagesavealpha($image, true);
        imagepng($image, $file, 9); // Máxima compresión
        imagedestroy($image);
    }

    private function optimizeGIF($file) {
        $image = imagecreatefromgif($file);
        imagegif($image, $file);
        imagedestroy($image);
    }

    private function generateManifest() {
        $manifest = [];
        
        // CSS Files
        $cssFiles = glob($this->publicPath . '/css/*.min.css');
        foreach ($cssFiles as $file) {
            $path = str_replace($this->publicPath, '', $file);
            $manifest[$path] = $path . '?id=' . hash_file('md5', $file);
        }

        // JS Files
        $jsFiles = glob($this->publicPath . '/js/**/*.min.js');
        foreach ($jsFiles as $file) {
            $path = str_replace($this->publicPath, '', $file);
            $manifest[$path] = $path . '?id=' . hash_file('md5', $file);
        }

        file_put_contents(
            $this->manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT)
        );
    }

    public function asset($path) {
        $manifest = json_decode(
            file_get_contents($this->manifestPath),
            true
        );

        return $manifest[$path] ?? $path;
    }
}