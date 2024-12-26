<?php
class BackupManager {
    private $db;
    private $backupPath;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->backupPath = BASE_PATH . '/backups/';
        $this->loadConfig();
        $this->ensureBackupDirectory();
    }

    public function createBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql.enc";
        $dump = $this->generateDump();
        $encrypted = $this->encrypt($dump);
        
        file_put_contents($this->backupPath . $filename, $encrypted);
        $this->cleanOldBackups();
        
        return $filename;
    }

    public function restoreBackup($filename) {
        $this->validateBackupFile($filename);
        $encrypted = file_get_contents($this->backupPath . $filename);
        $sql = $this->decrypt($encrypted);
        
        try {
            $this->db->beginTransaction();
            foreach (explode(';', $sql) as $statement) {
                if (trim($statement)) {
                    $this->db->exec($statement);
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Error en restauración: ' . $e->getMessage());
        }
    }

    public function getBackups() {
        $backups = [];
        foreach (glob($this->backupPath . '*.sql.enc') as $file) {
            $backups[] = [
                'filename' => basename($file),
                'fecha' => filemtime($file),
                'tamano' => filesize($file),
                'estado' => $this->validateBackupIntegrity($file) ? 'success' : 'warning'
            ];
        }
        return array_reverse($backups);
    }

    private function generateDump() {
        $dump = '';
        $tables = $this->getTables();
        
        foreach ($tables as $table) {
            $dump .= $this->getDDL($table);
            $dump .= $this->getData($table);
        }
        
        return $dump;
    }

    private function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $key = hash('sha256', $this->config['clave_cifrado'], true);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return $iv . $encrypted;
    }

    private function decrypt($data) {
        $ivSize = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivSize);
        $encrypted = substr($data, $ivSize);
        $key = hash('sha256', $this->config['clave_cifrado'], true);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }

    private function validateBackupIntegrity($file) {
        try {
            $encrypted = file_get_contents($file);
            $decrypted = $this->decrypt($encrypted);
            return strpos($decrypted, 'CREATE TABLE') !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function cleanOldBackups() {
        $retention = intval($this->config['dias_retencion']);
        foreach (glob($this->backupPath . '*.sql.enc') as $file) {
            if (time() - filemtime($file) > $retention * 86400) {
                unlink($file);
            }
        }
    }

    private function loadConfig() {
        $stmt = $this->db->query("SELECT * FROM configuracion WHERE clave LIKE 'backup_%'");
        $this->config = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = str_replace('backup_', '', $row['clave']);
            $this->config[$key] = $row['valor'];
        }
    }

    private function getTables() {
        $tables = [];
        $result = $this->db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    private function getDDL($table) {
        $createTable = $this->db->query("SHOW CREATE TABLE `$table`")->fetch();
        return $createTable[1] . ";\n\n";
    }

    private function getData($table) {
        $data = '';
        $result = $this->db->query("SELECT * FROM `$table`");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $data .= "INSERT INTO `$table` VALUES (" . 
                    implode(',', array_map([$this->db, 'quote'], $row)) . 
                    ");\n";
        }
        return $data . "\n";
    }
}