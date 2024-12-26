class UsuarioModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'usuarios';
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'contrasena',
        'rol',
        'telefono'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getPermisos($idUsuario) {
        $sql = "SELECT p.* 
                FROM permisos p 
                JOIN usuarios_permisos up ON p.id = up.id_permiso 
                WHERE up.id_usuario = ? 
                AND up.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function actualizarPermisos($idUsuario, $permisos) {
        $this->db->beginTransaction();
        try {
            // Desactiva permisos actuales
            $sql1 = "UPDATE usuarios_permisos SET deleted_at = CURRENT_TIMESTAMP WHERE id_usuario = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$idUsuario]);

            // Inserta nuevos permisos
            $sql2 = "INSERT INTO usuarios_permisos (id_usuario, id_permiso) VALUES (?, ?)";
            $stmt2 = $this->db->prepare($sql2);
            foreach ($permisos as $permiso) {
                $stmt2->execute([$idUsuario, $permiso]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}