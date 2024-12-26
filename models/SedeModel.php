<?php
class SedeModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'sedes';
    protected $fillable = [
        'nombre',
        'direccion'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getSedesActivas() {
        $sql = "SELECT s.*, 
                (SELECT COUNT(*) FROM aulas a WHERE a.id_sede = s.id AND a.deleted_at IS NULL) as total_aulas,
                (SELECT COUNT(*) FROM talleres t WHERE t.id_sede = s.id AND t.deleted_at IS NULL) as total_talleres
                FROM sedes s 
                WHERE s.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}