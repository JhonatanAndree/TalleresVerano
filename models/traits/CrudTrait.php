<?php
namespace Models\Traits;

trait CrudTrait {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $softDelete = true;

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        if ($this->softDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAll($filters = [], $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];

        if ($this->softDelete) {
            $where[] = "deleted_at IS NULL";
        }

        foreach ($filters as $field => $value) {
            if (in_array($field, $this->fillable)) {
                $where[] = "$field = ?";
                $params[] = $value;
            }
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT * FROM {$this->table} {$whereClause} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return [
            'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $this->count($filters),
            'page' => $page,
            'limit' => $limit
        ];
    }

    public function create(array $data) {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        
        $columns = implode(', ', array_keys($fields));
        $values = implode(', ', array_fill(0, count($fields), '?'));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($values)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($fields));
        
        return $this->db->lastInsertId();
    }

    public function update($id, array $data) {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        
        $set = implode(', ', array_map(function($field) {
            return "$field = ?";
        }, array_keys($fields)));
        
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";
        
        if ($this->softDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $values = array_values($fields);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id) {
        if ($this->softDelete) {
            $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE {$this->primaryKey} = ?";
        } else {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function restore($id) {
        if (!$this->softDelete) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    protected function count($filters = []) {
        $where = [];
        $params = [];

        if ($this->softDelete) {
            $where[] = "deleted_at IS NULL";
        }

        foreach ($filters as $field => $value) {
            if (in_array($field, $this->fillable)) {
                $where[] = "$field = ?";
                $params[] = $value;
            }
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['total'];
    }
}