<?php
class TallerModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'talleres';
    protected $fillable = [
        'nombre',
        'id_sede',
        'id_aula',
        'id_docente',
        'capacidad_maxima'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getTalleresDisponibles($idSede) {
        $sql = "SELECT t.*, a.nombre as aula_nombre, d.nombre as docente_nombre 
                FROM talleres t 
                LEFT JOIN aulas a ON t.id_aula = a.id 
                LEFT JOIN usuarios d ON t.id_docente = d.id 
                WHERE t.id_sede = ? AND t.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idSede]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function validarDisponibilidadHorario($idTaller, $idHorario) {
        $sql = "SELECT COUNT(*) as conflictos 
                FROM horarios_talleres 
                WHERE id_taller = ? AND id_horario = ? 
                AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idTaller, $idHorario]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['conflictos'] == 0;
    }
}