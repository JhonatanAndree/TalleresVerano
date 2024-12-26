<?php
class HorarioModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'horarios';
    protected $fillable = [
        'id_taller',
        'intervalo_tiempo',
        'turno'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function validarConflictos($idTaller, $intervaloTiempo, $turno) {
        $sql = "SELECT COUNT(*) as conflictos 
                FROM horarios h 
                JOIN talleres t ON h.id_taller = t.id 
                WHERE t.id_aula = (SELECT id_aula FROM talleres WHERE id = ?) 
                AND h.intervalo_tiempo = ? 
                AND h.turno = ? 
                AND h.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idTaller, $intervaloTiempo, $turno]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['conflictos'] == 0;
    }
}