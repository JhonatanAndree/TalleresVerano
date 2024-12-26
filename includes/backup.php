<?php
class BackupSystem {
    private $db;
    private $backupPath;
    private $encryptionKey;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->backupPath = dirname(__DIR__) . '/backups/';
        $this->encryptionKey = getenv('BACKUP_KEY') ?: 'your-256bit-key';
        
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    public function createBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $tables = $this->getTables();
        
        $dump = $this->generateDump($tables);
        $encrypted = $this->encrypt($dump);
        
        file_put_contents($this->backupPath . $filename . '.enc', $encrypted);
        createLog(0, 'backup', "Backup creado: {$filename}");
        
        $this->cleanOldBackups();
        return true;
    }

    private function getTables() {
        $tables = [];
        $result = $this->db->query("SHOW TABLES");
        while($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    private function generateDump($tables) {
        $dump = '';
        foreach($tables as $table) {
            $result = $this->db->query("SELECT * FROM {$table}");
            $rows = $result->fetchAll(PDO::FETCH_NUM);
            
            $dump .= "DROP TABLE IF EXISTS {$table};\n";
            $createTable = $this->db->query("SHOW CREATE TABLE {$table}")->fetch();
            $dump .= $createTable[1] . ";\n";
            
            foreach($rows as $row) {
                $dump .= "INSERT INTO {$table} VALUES(" . implode(',', array_map([$this, 'escapeString'], $row)) . ");\n";
            }
        }
        return $dump;
    }

    private function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function decrypt($data) {
        $data = base64_decode($data);
        $ivSize = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivSize);
        $encrypted = substr($data, $ivSize);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
    }

    private function cleanOldBackups($daysToKeep = 7) {
        $files = glob($this->backupPath . '*.enc');
        foreach($files as $file) {
            if(time() - filemtime($file) > $daysToKeep * 24 * 60 * 60) {
                unlink($file);
            }
        }
    }

    private function escapeString($str) {
        return $this->db->quote($str);
    }
}
?>