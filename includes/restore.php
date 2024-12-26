<?php
class RestoreSystem extends BackupSystem {
    public function restore($filename) {
        if (!file_exists($this->backupPath . $filename)) {
            throw new Exception('Archivo de backup no encontrado');
        }

        $encrypted = file_get_contents($this->backupPath . $filename);
        $sql = $this->decrypt($encrypted);
        
        try {
            $this->db->beginTransaction();
            $statements = array_filter(explode(';', $sql));
            
            foreach($statements as $statement) {
                if (trim($statement) != '') {
                    $this->db->exec($statement);
                }
            }
            
            $this->db->commit();
            createLog(0, 'restore', "Restauración exitosa: {$filename}");
            return true;
            
        } catch(PDOException $e) {
            $this->db->rollBack();
            createLog(0, 'restore', "Error en restauración: {$e->getMessage()}");
            throw $e;
        }
    }
}
?>