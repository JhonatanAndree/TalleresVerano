<?php
/**
 * Gestor de esquema de base de datos
 * Ruta: includes/database/SchemaManager.php
 */

class SchemaManager {
    private $db;
    private $logger;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->logger = ActivityLogger::getInstance();
    }

    public function executeMigrations($targetVersion = null) {
        try {
            $this->db->beginTransaction();

            $currentVersion = $this->getCurrentVersion();
            $migrations = $this->getPendingMigrations($currentVersion, $targetVersion);

            foreach ($migrations as $migration) {
                $this->executeMigration($migration);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Error en migración', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getCurrentVersion() {
        try {
            $stmt = $this->db->query("SELECT version FROM migrations ORDER BY id DESC LIMIT 1");
            return $stmt->fetchColumn() ?: 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    private function getPendingMigrations($currentVersion, $targetVersion = null) {
        $migrations = [];
        $path = __DIR__ . '/../../database/migrations';
        $files = glob($path . '/*.sql');

        foreach ($files as $file) {
            preg_match('/(\d+)_.*\.sql$/', $file, $matches);
            $version = (int)$matches[1];

            if ($version > $currentVersion && (!$targetVersion || $version <= $targetVersion)) {
                $migrations[$version] = $file;
            }
        }

        ksort($migrations);
        return $migrations;
    }

    private function executeMigration($file) {
        $sql = file_get_contents($file);
        $statements = $this->parseSQL($sql);

        foreach ($statements as $statement) {
            if (trim($statement)) {
                $this->db->exec($statement);
            }
        }

        preg_match('/(\d+)_.*\.sql$/', $file, $matches);
        $version = $matches[1];

        $stmt = $this->db->prepare("INSERT INTO migrations (version, name) VALUES (?, ?)");
        $stmt->execute([$version, basename($file)]);

        $this->logger->info('Migración ejecutada', ['version' => $version]);
    }

    private function parseSQL($sql) {
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $sql = preg_replace('/--[^\n]*\n/', '', $sql);
        return explode(';', $sql);
    }
}