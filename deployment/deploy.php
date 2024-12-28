<?php
/**
 * Script de despliegue
 * Ruta: deployment/deploy.php
 * Colocar en la raíz del proyecto
 * Ejecutar por primera vez: php deployment/deploy.php
 * Este script verifica requisitos y prepara el entorno
 */

class Deployer {
    private $config;
    private $logger;

    public function __construct() {
        $this->config = require __DIR__ . '/../Config/deploy.php';
        $this->logger = ActivityLogger::getInstance();
    }

    public function deploy() {
        try {
            $this->preDeploymentChecks();
            $this->backup();
            $this->optimizeAssets();
            $this->migrateDatabase();
            $this->updatePermissions();
            $this->clearCache();
            $this->notifyCompletion();
            return true;
        } catch (Exception $e) {
            $this->logger->error('Error en despliegue', ['error' => $e->getMessage()]);
            $this->rollback();
            return false;
        }
    }

    private function preDeploymentChecks() {
        // Verificar requerimientos
        $checks = [
            'PHP Version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'Extensions' => $this->checkExtensions(),
            'Permissions' => $this->checkPermissions(),
            'Database' => $this->checkDatabase(),
            'SSL' => $this->checkSSL()
        ];

        foreach ($checks as $check => $passed) {
            if (!$passed) {
                throw new Exception("Pre-deployment check failed: $check");
            }
        }
    }

    private function checkExtensions() {
        $required = ['pdo_mysql', 'gd', 'curl', 'mbstring', 'xml', 'zip'];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                return false;
            }
        }
        return true;
    }

    private function checkPermissions() {
        $paths = [
            'storage/logs',
            'storage/cache',
            'storage/uploads',
            'storage/backups'
        ];

        foreach ($paths as $path) {
            if (!is_writable($path)) {
                return false;
            }
        }
        return true;
    }

    private function checkDatabase() {
        try {
            $db = Database::getInstance()->getConnection();
            $db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function checkSSL() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    private function backup() {
        $backupService = new BackupService();
        return $backupService->createBackup();
    }

    private function optimizeAssets() {
        $assetManager = new AssetManager();
        return $assetManager->optimize();
    }

    private function migrateDatabase() {
        $schemaManager = new SchemaManager();
        return $schemaManager->executeMigrations();
    }

    private function updatePermissions() {
        $paths = [
            'storage' => 0755,
            'storage/logs' => 0755,
            'storage/cache' => 0755,
            'public/uploads' => 0755
        ];

        foreach ($paths as $path => $perm) {
            chmod($path, $perm);
        }
    }

    private function clearCache() {
        $cache = CacheManager::getInstance();
        return $cache->clear();
    }

    private function notifyCompletion() {
        $notification = new NotificationService();
        return $notification->notifyDeployment([
            'environment' => getenv('APP_ENV'),
            'version' => getenv('APP_VERSION'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function rollback() {
        // Implementar rollback en caso de error
    }
}