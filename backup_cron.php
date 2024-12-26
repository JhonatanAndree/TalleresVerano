<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/backup.php';

if (date('H:i') === '19:00') {
    $backup = new BackupSystem();
    $backup->createBackup();
}
?>