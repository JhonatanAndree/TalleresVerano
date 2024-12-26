<?php
class ConfiguracionModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'configuracion';
    protected $fillable = [
        'nombre_sistema',
        'logotipo',
        'contacto',
        'moneda',
        'ano_fiscal'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function getConfiguracionActual() {
        $sql = "SELECT * FROM configuracion WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function actualizarAnoFiscal($ano) {
        $this->db->beginTransaction();
        try {
            // Desactiva año fiscal actual
            $sql1 = "UPDATE configuracion SET activo = 0 WHERE ano_fiscal != ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$ano]);

            // Activa nuevo año fiscal
            $sql2 = "UPDATE configuracion SET activo = 1 WHERE ano_fiscal = ?";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([$ano]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
