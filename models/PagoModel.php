<?php
class PagoModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'pagos';
    protected $fillable = [
        'id_estudiante',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'codigo_transaccion',
        'estado'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function verificarPago($idEstudiante, $idTaller) {
        $sql = "SELECT p.* 
                FROM pagos p 
                WHERE p.id_estudiante = ? 
                AND p.id_taller = ? 
                AND p.estado = 'completado' 
                AND p.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idEstudiante, $idTaller]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}