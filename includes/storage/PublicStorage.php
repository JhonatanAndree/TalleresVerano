<?php
/**
 * Gestor de archivos públicos
 * Ruta: includes/storage/PublicStorage.php
 */

class PublicStorage {
    private $publicPath;
    private $allowedTypes;
    private $logger;

    public function __construct() {
        $this->publicPath = __DIR__ . '/../../public/uploads';
        $this->allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $this->logger = ActivityLogger::getInstance();
        $this->initializeDirectories();
    }

    private function initializeDirectories() {
        if (!file_exists($this->publicPath)) {
            mkdir($this->publicPath, 0755, true);
        }
    }

    public function storePublicFile($file, $directory = '') {
        try {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, $this->allowedTypes)) {
                throw new Exception('Tipo de archivo no permitido');
            }

            $filename = $this->generateFilename($extension);
            $targetPath = $this->getTargetPath($directory, $filename);

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Error al mover el archivo');
            }

            $publicUrl = $this->getPublicUrl($directory, $filename);
            
            $this->logger->info('Archivo público almacenado', [
                'filename' => $filename,
                'url' => $publicUrl
            ]);

            return [
                'success' => true,
                'url' => $publicUrl,
                'filename' => $filename
            ];
        } catch (Exception $e) {
            $this->logger->error('Error almacenando archivo público', [
                'error' => $e->getMessage(),
                'file' => $file['name']
            ]);
            throw $e;
        }
    }

    private function generateFilename($extension) {
        return sprintf(
            '%s_%s.%s',
            uniqid(),
            time(),
            $extension
        );
    }

    private function getTargetPath($directory, $filename) {
        $directory = trim($directory, '/');
        $path = $this->publicPath;
        
        if ($directory) {
            $path .= '/' . $directory;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }

        return $path . '/' . $filename;
    }

    private function getPublicUrl($directory, $filename) {
        $directory = trim($directory, '/');
        return '/uploads/' . ($directory ? $directory . '/' : '') . $filename;
    }

    public function deletePublicFile($url) {
        $path = $this->publicPath . str_replace('/uploads', '', $url);
        if (file_exists($path) && is_file($path)) {
            unlink($path);
            $this->logger->info('Archivo público eliminado', ['url' => $url]);
            return true;
        }
        return false;
    }
}