<?php
require_once '../includes/auth.php';
require_once '../includes/backup/BackupManager.php';

class BackupController {
    private $backupManager;

    public function __construct() {
        if (!checkPermission('SuperAdmin')) {
            http_response_code(403);
            exit(json_encode(['success' => false, 'message' => 'No autorizado']));
        }
        $this->backupManager = new BackupManager();
    }

    public function handleRequest() {
        $action = $_REQUEST['action'] ?? '';
        
        switch($action) {
            case 'list':
                return $this->listBackups();
            case 'generate':
                return $this->generateBackup();
            case 'restore':
                return $this->restoreBackup();
            case 'saveConfig':
                return $this->saveConfig();
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }

    private function listBackups() {
        return ['success' => true, 'backups' => $this->backupManager->getBackups()];
    }

    private function generateBackup() {
        try {
            $filename = $this->backupManager->createBackup();
            createLog($_SESSION['user_id'], 'backup', "Backup generado: $filename");
            return ['success' => true, 'filename' => $filename];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function restoreBackup() {
        if (empty($_POST['filename'])) {
            return ['success' => false, 'message' => 'Nombre de archivo requerido'];
        }

        try {
            $this->backupManager->restoreBackup($_POST['filename']);
            createLog($_SESSION['user_id'], 'backup', "Backup restaurado: {$_POST['filename']}");
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function saveConfig() {
        $config = [
            'hora_backup' => $_POST['hora_backup'],
            'dias_retencion' => $_POST['dias_retencion'],
            'clave_cifrado' => $_POST['clave_cifrado']
        ];

        try {
            $this->backupManager->saveConfig($config);
            createLog($_SESSION['user_id'], 'backup', 'Configuración actualizada');
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

$controller = new BackupController();
echo json_encode($controller->handleRequest());