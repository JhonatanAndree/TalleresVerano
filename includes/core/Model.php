<?php
/**
 * Modelo base
 * Ruta: includes/core/Model.php
 */

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = ['deleted_at'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $softDelete = true;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        if ($this->softDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        if ($this->softDelete) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $data = $this->filterFillable($data);
        $fields = array_keys($data);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', array_fill(0, count($fields), '?')) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->find($this->db->lastInsertId());
    }

    public function update($id, array $data) {
        $data = $this->filterFillable($data);
        $fields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($data));
        
        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $fields) . " 
                WHERE {$this->primaryKey} = ?";
                
        if ($this->softDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id) {
        if ($this->softDelete) {
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function restore($id) {
        if (!$this->softDelete) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET deleted_at = NULL 
                WHERE {$this->primaryKey} = ?";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    protected function filterFillable(array $data) {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function beginTransaction() {
        $this->db->beginTransaction();
    }

    protected function commit() {
        $this->db->commit();
    }

    protected function rollBack() {
        $this->db->rollBack();
    }

    protected function formatDates($data) {
        foreach ($this->dates as $field) {
            if (isset($data[$field])) {
                $data[$field] = date('Y-m-d H:i:s', strtotime($data[$field]));
            }
        }
        return $data;
    }

    protected function hideFields($data) {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        return $data;
    }
}