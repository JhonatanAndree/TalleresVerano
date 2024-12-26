<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/backup/BackupManager.php';

// Verificar hora programada (7:00 PM UTC-5)
$horaActual = date('H:i');
$horaProgramada = '19:00';

if ($horaActual === $horaProgramada) {
    try {
        $backupManager = new BackupManager();
        $filename = $backupManager->createBackup();
        
        // Log del backup
        file_put_contents(
            dirname(__DIR__) . '/logs/backup.log',
            date('Y-m-d H:i:s') . " - Backup automático creado: $filename\n",
            FILE_APPEND
        );
    } catch (Exception $e) {
        file_put_contents(
            dirname(__DIR__) . '/logs/backup_error.log',
            date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }
}