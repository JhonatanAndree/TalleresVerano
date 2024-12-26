<?php
/**
 * Modelo de Notificaciones
 * Ruta: Models/NotificacionModel.php
 */

class NotificacionModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'notificaciones';
    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'estado',
        'data'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function crearNotificacionMatricula($matriculaId) {
        $sql = "SELECT 
                    m.*,
                    e.nombre as estudiante_nombre,
                    t.nombre as taller_nombre
                FROM matriculas m
                JOIN estudiantes e ON m.estudiante_id = e.id
                JOIN talleres t ON m.taller_id = t.id
                WHERE m.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matriculaId]);
        $matricula = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->create([
            'usuario_id' => $matricula['estudiante_id'],
            'tipo' => 'matricula',
            'titulo' => 'Matrícula Registrada',
            'mensaje' => "Se ha registrado exitosamente en el taller {$matricula['taller_nombre']}",
            'estado' => 'pendiente',
            'data' => json_encode($matricula)
        ]);
    }

    public function getNotificacionesUsuario($usuarioId, $estado = null) {
        $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? AND deleted_at IS NULL";
        $params = [$usuarioId];

        if ($estado) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function marcarComoLeida($id) {
        return $this->update($id, ['estado' => 'leida']);
    }

    public function marcarTodasComoLeidas($usuarioId) {
        $sql = "UPDATE notificaciones 
                SET estado = 'leida', 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE usuario_id = ? 
                AND estado = 'pendiente'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuarioId]);
    }

    public function enviarNotificacionWhatsApp($notificacionId) {
        $sql = "SELECT n.*, u.telefono 
                FROM notificaciones n
                JOIN usuarios u ON n.usuario_id = u.id
                WHERE n.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notificacionId]);
        $notificacion = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($notificacion && $notificacion['telefono']) {
            $whatsapp = new WhatsAppService();
            return $whatsapp->enviarMensaje(
                $notificacion['telefono'],
                $notificacion['mensaje']
            );
        }

        return false;
    }
}