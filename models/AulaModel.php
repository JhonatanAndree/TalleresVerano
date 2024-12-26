<?php
class AulaModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'aulas';
    protected $fillable = [
        'nombre',
        'capacidad',
        'id_sede'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getAulasDisponibles($idSede, $idHorario) {
        $sql = "SELECT a.* 
                FROM aulas a 
                LEFT JOIN talleres t ON a.id = t.id_aula 
                LEFT JOIN horarios_talleres ht ON t.id = ht.id_taller 
                WHERE a.id_sede = ? 
                AND a.deleted_at IS NULL 
                AND (ht.id_horario != ? OR ht.id_horario IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idSede, $idHorario]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}