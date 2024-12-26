<?php
/**
 * Optimizador de consultas SQL
 * Ruta: includes/database/QueryOptimizer.php
 */

class QueryOptimizer {
    private $db;
    private $logger;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->logger = ActivityLogger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function analyzeQuery($sql) {
        $stmt = $this->db->prepare("EXPLAIN $sql");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function optimizeQuery($sql) {
        $analysis = $this->analyzeQuery($sql);
        $optimizedSql = $sql;

        if ($this->needsOptimization($analysis)) {
            $optimizedSql = $this->applyOptimizations($sql, $analysis);
            $this->logger->info('Query optimizada', [
                'original' => $sql,
                'optimized' => $optimizedSql
            ]);
        }

        return $optimizedSql;
    }

    private function needsOptimization($analysis) {
        return (
            $analysis['rows'] > 1000 || 
            $analysis['type'] == 'ALL' ||
            strpos($analysis['Extra'], 'Using filesort') !== false ||
            strpos($analysis['Extra'], 'Using temporary') !== false
        );
    }

    private function applyOptimizations($sql, $analysis) {
        $sql = $this->optimizeJoins($sql);
        $sql = $this->addIndexHints($sql, $analysis);
        $sql = $this->optimizeGroupBy($sql);
        $sql = $this->optimizeOrderBy($sql);
        return $sql;
    }

    private function optimizeJoins($sql) {
        // Convertir LEFT JOIN a INNER JOIN cuando sea posible
        if (strpos($sql, 'LEFT JOIN') !== false && 
            strpos($sql, 'WHERE') !== false && 
            strpos($sql, 'IS NOT NULL') !== false) {
            $sql = str_replace('LEFT JOIN', 'INNER JOIN', $sql);
        }
        return $sql;
    }

    private function addIndexHints($sql, $analysis) {
        if ($analysis['possible_keys'] !== null) {
            $keys = explode(',', $analysis['possible_keys']);
            $bestKey = trim($keys[0]);
            $tableName = $analysis['table'];
            
            if (strpos($sql, "FROM $tableName") !== false) {
                $sql = str_replace(
                    "FROM $tableName",
                    "FROM $tableName USE INDEX ($bestKey)",
                    $sql
                );
            }
        }
        return $sql;
    }

    private function optimizeGroupBy($sql) {
        if (strpos($sql, 'GROUP BY') !== false) {
            // Eliminar columnas innecesarias en GROUP BY
            preg_match('/GROUP BY (.+?)(?:ORDER BY|LIMIT|$)/i', $sql, $matches);
            if (isset($matches[1])) {
                $groupColumns = explode(',', $matches[1]);
                $essentialColumns = array_filter(array_map('trim', $groupColumns), function($col) use ($sql) {
                    return strpos($sql, "MIN($col)") !== false || 
                           strpos($sql, "MAX($col)") !== false || 
                           strpos($sql, "COUNT($col)") !== false;
                });
                if (!empty($essentialColumns)) {
                    $sql = str_replace($matches[1], implode(',', $essentialColumns), $sql);
                }
            }
        }
        return $sql;
    }

    private function optimizeOrderBy($sql) {
        if (strpos($sql, 'ORDER BY') !== false && strpos($sql, 'LIMIT') !== false) {
            // Usar índices para ORDER BY cuando hay LIMIT
            preg_match('/ORDER BY (.+?)(?:LIMIT|$)/i', $sql, $matches);
            if (isset($matches[1])) {
                $orderColumns = explode(',', $matches[1]);
                foreach ($orderColumns as &$column) {
                    $column = trim($column);
                    if (strpos($column, '.') === false) {
                        $tableAlias = $this->getMainTableAlias($sql);
                        if ($tableAlias) {
                            $column = "$tableAlias.$column";
                        }
                    }
                }
                $sql = str_replace($matches[1], implode(',', $orderColumns), $sql);
            }
        }
        return $sql;
    }

    private function getMainTableAlias($sql) {
        preg_match('/FROM\s+(\w+)(?:\s+AS\s+)?(\w+)?/i', $sql, $matches);
        return isset($matches[2]) ? $matches[2] : (isset($matches[1]) ? $matches[1] : null);
    }

    public function suggestIndexes($sql) {
        $analysis = $this->analyzeQuery($sql);
        $suggestions = [];

        if ($analysis['key'] === null) {
            preg_match('/WHERE\s+(.+?)(?:GROUP BY|ORDER BY|LIMIT|$)/i', $sql, $matches);
            if (isset($matches[1])) {
                $whereConditions = $matches[1];
                preg_match_all('/(\w+\.\w+|\w+)\s*(?:=|>|<|LIKE|IN)/i', $whereConditions, $columns);
                if (!empty($columns[1])) {
                    $suggestions[] = [
                        'table' => $analysis['table'],
                        'columns' => array_unique($columns[1]),
                        'type' => 'INDEX'
                    ];
                }
            }
        }

        return $suggestions;
    }
}