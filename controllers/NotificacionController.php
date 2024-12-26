<?php
class NotificacionController {
    private $notificacionModel;

    public function __construct() {
        $this->notificacionModel = new NotificacionModel();
    }

    public function obtenerNotificaciones() {
        try {
            $notificaciones = $this->notificacionModel->obtenerNotificaciones($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function marcarComoLeida() {
        try {
            $notificacion_id = $_POST['notificacion_id'];
            $this->notificacionModel->marcarComoLeida($notificacion_id);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function notificarConflictoHorario($datos) {
        return $this->notificacionModel->crearNotificacion([
            'usuario_id' => $datos['usuario_id'],
            'tipo' => 'conflicto_horario',
            'mensaje' => "Conflicto de horario detectado en el taller {$datos['taller']}",
            'enlace' => "/views/admin/horarios.php?taller_id={$datos['taller_id']}"
        ]);
    }
}