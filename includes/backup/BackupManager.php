<?php
/**
 * Gestor de respaldos de base de datos
 * Ruta: includes/backup/BackupManager.php
 */

class BackupManager {
    private $db;
    private $config;
    private $logger;
    private $driveService;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->config = require __DIR__ . '/../../Config/backup.php';
        $this->logger = ActivityLogger::getInstance();
        $this->driveService = new GoogleDriveService();
    }

    public function createBackup() {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->config['backup_path'] . '/' . $filename;

            // Ejecutar mysqldump
            $command = sprintf(
                'mysqldump --opt -h %s -u %s -p%s %s > %s',
                escapeshellarg($this->config['db_host']),
                escapeshellarg($this->config['db_user']),
                escapeshellarg($this->config['db_pass']),
                escapeshellarg($this->config['db_name']),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception('Error al crear backup');
            }

            // Comprimir backup
            if ($this->config['compression']) {
                $this->compressBackup($filepath);
                $filepath .= '.gz';
                $filename .= '.gz';
            }

            // Subir a Google Drive
            if ($this->config['drive_backup']) {
                $driveFileId = $this->driveService->uploadFile($filepath, [
                    'name' => $filename,
                    'parents' => [$this->config['drive_folder_id']]
                ]);
            }

            // Registrar backup
            $this->registerBackup([
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'drive_file_id' => $driveFileId ?? null
            ]);

            // Limpiar backups antiguos
            $this->cleanOldBackups();

            $this->logger->info('Backup creado exitosamente', [
                'filename' => $filename,
                'size' => filesize($filepath)
            ]);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Error creando backup', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function compressBackup($filepath) {
        $handle = fopen($filepath, 'r');
        $compressed = gzopen($filepath . '.gz', 'w9');
        
        while (!feof($handle)) {
            gzwrite($compressed, fread($handle, 1024 * 512));
        }
        
        fclose($handle);
        gzclose($compressed);
        unlink($filepath);
    }

    private function registerBackup($data) {
        $sql = "INSERT INTO backups (filename, filepath, size, drive_file_id, created_at) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['filename'],
            $data['filepath'],
            $data['size'],
            $data['drive_file_id']
        ]);
    }

    private function cleanOldBackups() {
        foreach (['daily', 'weekly', 'monthly'] as $type) {
            $retention = $this->config['retention'][$type];
            $period = $this->getRetentionPeriod($type);
            
            $sql = "SELECT * FROM backups 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? $period) 
                    ORDER BY created_at DESC 
                    LIMIT ?, 999999";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$retention, $retention]);
            $backupsToDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($backupsToDelete as $backup) {
                $this->deleteBackup($backup);
            }
        }
    }

    private function deleteBackup($backup) {
        // Eliminar archivo local
        if (file_exists($backup['filepath'])) {
            unlink($backup['filepath']);
        }

        // Eliminar de Google Drive
        if ($backup['drive_file_id']) {
            $this->driveService->deleteFile($backup['drive_file_id']);
        }

        // Eliminar registro
        $sql = "DELETE FROM backups WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$backup['id']]);
    }

    private function getRetentionPeriod($type) {
        switch ($type) {
            case 'daily': return 'DAY';
            case 'weekly': return 'WEEK';
            case 'monthly': return 'MONTH';
            default: throw new Exception('Tipo de retención inválido');
        }
    }
}