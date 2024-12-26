<?php
class DocenteModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'usuarios';
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'contrasena',
        'telefono'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getDocentesDisponibles($idHorario) {
        $sql = "SELECT u.* 
                FROM usuarios u 
                LEFT JOIN talleres t ON u.id = t.id_docente 
                LEFT JOIN horarios_talleres ht ON t.id = ht.id_taller 
                WHERE u.rol = 'Docente' 
                AND u.deleted_at IS NULL 
                AND (ht.id_horario != ? OR ht.id_horario IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idHorario]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTalleresAsignados($idDocente) {
        $sql = "SELECT t.*, s.nombre as sede_nombre, a.nombre as aula_nombre 
                FROM talleres t 
                JOIN sedes s ON t.id_sede = s.id 
                JOIN aulas a ON t.id_aula = a.id 
                WHERE t.id_docente = ? AND t.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idDocente]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}