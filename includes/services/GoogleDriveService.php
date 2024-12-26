<?php
class GoogleDriveService {
    private $service;
    private $logger;

    public function __construct() {
        $this->initializeService();
        $this->logger = ActivityLogger::getInstance();
    }

    private function initializeService() {
        $client = new Google_Client();
        $client->setAuthConfig(__DIR__ . '/../../Config/google-credentials.json');
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $this->service = new Google_Service_Drive($client);
    }

    public function uploadFile($filepath, $mimeType, $folderId) {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => basename($filepath),
                'parents' => [$folderId]
            ]);

            $content = file_get_contents($filepath);
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart'
            ]);

            return $file->getId();
        } catch (Exception $e) {
            $this->logger->error('Error uploading to Drive', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function downloadFile($fileId, $destinationPath) {
        try {
            $response = $this->service->files->get($fileId, ['alt' => 'media']);
            $content = $response->getBody()->getContents();
            file_put_contents($destinationPath, $content);
        } catch (Exception $e) {
            $this->logger->error('Error downloading from Drive', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteFile($fileId) {
        try {
            $this->service->files->delete($fileId);
        } catch (Exception $e) {
            $this->logger->error('Error deleting from Drive', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function listFiles($folderId) {
        try {
            $query = "'" . $folderId . "' in parents";
            $files = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name, createdTime, size)'
            ]);
            return $files->getFiles();
        } catch (Exception $e) {
            $this->logger->error('Error listing Drive files', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}