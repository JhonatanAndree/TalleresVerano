<?php
class UsuarioController {
    private $model;
    private $security;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new UsuarioModel($db);
        $this->security = SecurityHelper::getInstance();
        $this->logger = require_once __DIR__ . '/../includes/logger/ActivityLogger.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->validateUsuarioData($_POST);
                $data['contrasena'] = $this->security->hashPassword($_POST['contrasena']);

                $id = $this->model->create($data);

                if (!empty($_POST['permisos'])) {
                    $this->model->actualizarPermisos($id, $_POST['permisos']);
                }

                $this->logger->info('Usuario creado', ['id' => $id]);

                header('Location: /admin/usuarios');
                exit;
            } catch (Exception $e) {
                require_once __DIR__ . '/../views/admin/usuarios/create.php';
            }
        } else {
            require_once __DIR__ . '/../views/admin/usuarios/create.php';
        }
    }

    public function updatePermisos() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
            $this->security->checkPermission('usuarios', 'update')) {
            try {
                $idUsuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
                $permisos = array_map('intval', $_POST['permisos'] ?? []);

                if ($this->model->actualizarPermisos($idUsuario, $permisos)) {
                    $this->logger->info('Permisos actualizados', [
                        'id_usuario' => $idUsuario,
                        'permisos' => $permisos
                    ]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error al actualizar permisos']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}