<?php
/**
 * Gestor del sistema de archivos
 * Ruta: includes/storage/StorageManager.php
 */

class StorageManager {
    private $basePath;
    private $allowedTypes;
    private $maxFileSize;
    private $logger;

    public function __construct() {
        $this->basePath = __DIR__ . '/../../storage';
        $this->allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'text/csv' => 'csv',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
        ];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
        $this->logger = ActivityLogger::getInstance();
        $this->initializeDirectories();
    }

    private function initializeDirectories() {
        $directories = ['uploads', 'temp', 'backups', 'public', 'documents'];
        foreach ($directories as $dir) {
            $path = $this->basePath . '/' . $dir;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    public function store($file, $directory, $filename = null) {
        try {
            $this->validateFile($file);
            $extension = $this->allowedTypes[$file['type']];
            $filename = $filename ?: $this->generateFilename($extension);
            $targetPath = $this->getTargetPath($directory, $filename);

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Error al mover el archivo');
            }

            $this->logger->info('Archivo almacenado', [
                'filename' => $filename,
                'path' => $targetPath
            ]);

            return [
                'success' => true,
                'path' => $this->getRelativePath($targetPath),
                'filename' => $filename
            ];
        } catch (Exception $e) {
            $this->logger->error('Error almacenando archivo', [
                'error' => $e->getMessage(),
                'file' => $file['name']
            ]);
            throw $e;
        }
    }

    public function delete($path) {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
            $this->logger->info('Archivo eliminado', ['path' => $path]);
            return true;
        }
        return false;
    }

    public function exists($path) {
        return file_exists($this->basePath . '/' . ltrim($path, '/'));
    }

    public function get($path) {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        if (!file_exists($fullPath)) {
            throw new Exception('Archivo no encontrado');
        }
        return file_get_contents($fullPath);
    }

    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error en la carga del archivo');
        }

        if (!isset($this->allowedTypes[$file['type']])) {
            throw new Exception('Tipo de archivo no permitido');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('El archivo excede el tamaño máximo permitido');
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
        $path = $this->basePath . '/' . $directory;

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $path . '/' . $filename;
    }

    private function getRelativePath($fullPath) {
        return str_replace($this->basePath, '', $fullPath);
    }

    public function createDirectory($path) {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
            return true;
        }
        return false;
    }

    public function listDirectory($path = '') {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        if (!is_dir($fullPath)) {
            throw new Exception('Directorio no encontrado');
        }

        $files = scandir($fullPath);
        $result = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $filePath = $fullPath . '/' . $file;
            $result[] = [
                'name' => $file,
                'path' => $this->getRelativePath($filePath),
                'type' => is_dir($filePath) ? 'directory' : 'file',
                'size' => is_file($filePath) ? filesize($filePath) : null,
                'modified' => filemtime($filePath)
            ];
        }

        return $result;
    }

    public function moveFile($source, $destination) {
        $sourcePath = $this->basePath . '/' . ltrim($source, '/');
        $destinationPath = $this->basePath . '/' . ltrim($destination, '/');

        if (!file_exists($sourcePath)) {
            throw new Exception('Archivo origen no encontrado');
        }

        $destinationDir = dirname($destinationPath);
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        return rename($sourcePath, $destinationPath);
    }
}