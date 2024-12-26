<?php
/**
 * Servicio de Google Drive
 * Ruta: includes/api/DriveService.php
 */

class DriveService {
    private $client;
    private $service;
    private $logger;
    private $config;
    private static $instance = null;

    private function __construct() {
        $this->config = require __DIR__ . '/../../Config/services.php';
        $this->logger = ActivityLogger::getInstance();
        $this->initializeClient();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeClient() {
        $this->client = new Google_Client();
        $this->client->setAuthConfig($this->config['google_drive']);
        $this->client->addScope(Google_Service_Drive::DRIVE_FILE);
        
        $this->service = new Google_Service_Drive($this->client);
    }

    public function uploadFile($filePath, $options = []) {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $options['name'] ?? basename($filePath),
                'parents' => [$options['folder_id'] ?? $this->config['google_drive']['folder_id']]
            ]);

            $content = file_get_contents($filePath);
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $options['mimeType'] ?? mime_content_type($filePath),
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            $this->logger->info('Archivo subido a Drive', [
                'file_id' => $file->id,
                'name' => $fileMetadata['name']
            ]);

            return [
                'success' => true,
                'file_id' => $file->id
            ];
        } catch (Exception $e) {
            $this->logger->error('Error subiendo archivo a Drive', [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);
            throw $e;
        }
    }

    public function downloadFile($fileId, $destinationPath) {
        try {
            $response = $this->service->files->get($fileId, ['alt' => 'media']);
            $content = $response->getBody()->getContents();
            
            file_put_contents($destinationPath, $content);
            return [
                'success' => true,
                'path' => $destinationPath
            ];
        } catch (Exception $e) {
            $this->logger->error('Error descargando archivo de Drive', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            throw $e;
        }
    }

    public function createFolder($name, $parentId = null) {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentId ?? $this->config['google_drive']['folder_id']]
            ]);

            $folder = $this->service->files->create($fileMetadata, ['fields' => 'id']);
            return [
                'success' => true,
                'folder_id' => $folder->id
            ];
        } catch (Exception $e) {
            $this->logger->error('Error creando carpeta en Drive', [
                'error' => $e->getMessage(),
                'name' => $name
            ]);
            throw $e;
        }
    }

    public function deleteFile($fileId) {
        try {
            $this->service->files->delete($fileId);
            return ['success' => true];
        } catch (Exception $e) {
            $this->logger->error('Error eliminando archivo de Drive', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            throw $e;
        }
    }

    public function listFiles($folderId = null, $pageSize = 100) {
        try {
            $query = sprintf(
                "trashed = false and '%s' in parents",
                $folderId ?? $this->config['google_drive']['folder_id']
            );

            $files = $this->service->files->listFiles([
                'q' => $query,
                'pageSize' => $pageSize,
                'fields' => 'files(id, name, mimeType, size, createdTime)'
            ]);

            return [
                'success' => true,
                'files' => $files->getFiles()
            ];
        } catch (Exception $e) {
            $this->logger->error('Error listando archivos de Drive', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}