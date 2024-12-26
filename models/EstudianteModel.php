<?php
namespace Models;

use Models\Interfaces\CrudInterface;
use Models\Traits\CrudTrait;

class EstudianteModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'estudiantes';
    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'edad',
        'genero',
        'id_padre',
        'id_taller'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getByDNI($dni) {
        $sql = "SELECT e.*, t.nombre as taller_nombre, s.nombre as sede_nombre 
                FROM estudiantes e
                LEFT JOIN talleres t ON e.id_taller = t.id
                LEFT JOIN sedes s ON t.id_sede = s.id
                WHERE e.dni = ? AND e.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dni]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByPadre($idPadre) {
        $sql = "SELECT e.*, t.nombre as taller_nombre
                FROM estudiantes e
                LEFT JOIN talleres t ON e.id_taller = t.id
                WHERE e.id_padre = ? AND e.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idPadre]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function validarCupo($idTaller) {
        $sql = "SELECT t.capacidad_maxima, COUNT(e.id) as inscritos
                FROM talleres t
                LEFT JOIN estudiantes e ON t.id = e.id_taller
                WHERE t.id = ? AND e.deleted_at IS NULL
                GROUP BY t.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idTaller]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'disponible' => $result['capacidad_maxima'] > $result['inscritos'],
            'cupos_restantes' => $result['capacidad_maxima'] - $result['inscritos']
        ];
    }
}