<?php
class YapeModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'pagos_yape';
    protected $fillable = [
        'estudiante_id',
        'taller_id',
        'monto',
        'transaction_id',
        'estado',
        'detalles_pago'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function createPendingPago($data) {
        $data['estado'] = 'pendiente';
        return $this->create($data);
    }

    public function updatePagoStatus($transactionId, $estado, $detalles = null) {
        $sql = "UPDATE {$this->table} 
                SET estado = ?, 
                    detalles_pago = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE transaction_id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $estado,
            $detalles ? json_encode($detalles) : null,
            $transactionId
        ]);
    }

    public function getPagosByEstudiante($estudianteId) {
        $sql = "SELECT py.*, t.nombre as taller_nombre 
                FROM {$this->table} py
                JOIN talleres t ON py.taller_id = t.id
                WHERE py.estudiante_id = ? AND py.deleted_at IS NULL
                ORDER BY py.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estudianteId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function verifyPagoCompleto($estudianteId, $tallerId) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE estudiante_id = ? 
                AND taller_id = ? 
                AND estado = 'completado' 
                AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estudianteId, $tallerId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }
}