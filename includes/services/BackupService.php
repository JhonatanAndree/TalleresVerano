<?php
/**
 * Servicio de Backup
 * Ruta: includes/services/BackupService.php
 */

class BackupService {
    private $db;
    private $backupPath;
    private $driveService;
    private $logger;
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../../Config/backup.php';
        $this->backupPath = $this->config['backup_path'];
        $this->db = require __DIR__ . '/../../Config/db.php';
        $this->driveService = new GoogleDriveService();
        $this->logger = ActivityLogger::getInstance();
    }

    public function crearBackup() {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupPath . '/' . $filename;

            // Crear directorio si no existe
            if (!is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0755, true);
            }

            // Ejecutar mysqldump
            $command = sprintf(
                'mysqldump --user=%s --password=%s %s > %s',
                escapeshellarg($this->config['db_user']),
                escapeshellarg($this->config['db_pass']),
                escapeshellarg($this->config['db_name']),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Error creating backup');
            }

            // Encriptar backup
            $encryptedFilepath = $this->encriptarArchivo($filepath);

            // Subir a Google Drive
            $driveFileId = $this->driveService->uploadFile(
                $encryptedFilepath,
                'application/octet-stream',
                $this->config['drive_folder_id']
            );

            // Registrar backup
            $this->registrarBackup([
                'filename' => $filename,
                'filepath' => $encryptedFilepath,
                'drive_file_id' => $driveFileId,
                'size' => filesize($encryptedFilepath)
            ]);

            // Eliminar archivos temporales
            unlink($filepath);
            unlink($encryptedFilepath);

            $this->logger->info('Backup created successfully', [
                'filename' => $filename,
                'drive_file_id' => $driveFileId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error creating backup', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function encriptarArchivo($filepath) {
        $key = base64_decode($this->config['encryption_key']);
        $iv = random_bytes(16);
        $encryptedFilepath = $filepath . '.enc';

        $fpOut = fopen($encryptedFilepath, 'wb');
        fwrite($fpOut, $iv);

        $fpIn = fopen($filepath, 'rb');
        while (!feof($fpIn)) {
            $plaintext = fread($fpIn, 16 * 1024); // 16KB por bloque
            $ciphertext = openssl_encrypt(
                $plaintext,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );
            fwrite($fpOut, $ciphertext);
        }

        fclose($fpIn);
        fclose($fpOut);

        return $encryptedFilepath;
    }

    public function restaurarBackup($backupId) {
        try {
            // Obtener información del backup
            $backup = $this->obtenerBackup($backupId);
            if (!$backup) {
                throw new \Exception('Backup not found');
            }

            // Descargar de Google Drive
            $encryptedFilepath = $this->backupPath . '/temp_' . uniqid() . '.sql.enc';
            $this->driveService->downloadFile($backup['drive_file_id'], $encryptedFilepath);

            // Desencriptar
            $decryptedFilepath = $this->desencriptarArchivo($encryptedFilepath);

            // Restaurar base de datos
            $command = sprintf(
                'mysql --user=%s --password=%s %s < %s',
                escapeshellarg($this->config['db_user']),
                escapeshellarg($this->config['db_pass']),
                escapeshellarg($this->config['db_name']),
                escapeshellarg($decryptedFilepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Error restoring backup');
            }

            // Limpiar archivos temporales
            unlink($encryptedFilepath);
            unlink($decryptedFilepath);

            $this->logger->info('Backup restored successfully', [
                'backup_id' => $backupId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error restoring backup', [
                'error' => $e->getMessage(),
                'backup_id' => $backupId
            ]);
            throw $e;
        }
    }

    private function desencriptarArchivo($encryptedFilepath) {
        $key = base64_decode($this->config['encryption_key']);
        $decryptedFilepath = str_replace('.enc', '', $encryptedFilepath);

        $fpIn = fopen($encryptedFilepath, 'rb');
        $iv = fread($fpIn, 16);

        $fpOut = fopen($decryptedFilepath, 'wb');

        while (!feof($fpIn)) {
            $ciphertext = fread($fpIn, 16 * 1024 + 16); // 16KB + 16 bytes de padding
            $plaintext = openssl_decrypt(
                $ciphertext,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );
            fwrite($fpOut, $plaintext);
        }

        fclose($fpIn);
        fclose($fpOut);

        return $decryptedFilepath;
    }

    private function registrarBackup($data) {
        $sql = "INSERT INTO backups (filename, filepath, drive_file_id, size, created_at) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['filename'],
            $data['filepath'],
            $data['drive_file_id'],
            $data['size']
        ]);
    }

    private function obtenerBackup($id) {
        $sql = "SELECT * FROM backups WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}