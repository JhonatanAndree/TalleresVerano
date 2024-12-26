<?php
class DocenteController {
    private $model;
    private $security;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new DocenteModel($db);
        $this->security = SecurityHelper::getInstance();
        $this->logger = require_once __DIR__ . '/../includes/logger/ActivityLogger.php';
    }

    public function dashboard() {
        $idDocente = $_SESSION['user']['id'];
        $talleres = $this->model->getTalleresAsignados($idDocente);
        require_once __DIR__ . '/../views/docente/dashboard.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->validateDocenteData($_POST);
                $data['contrasena'] = $this->security->hashPassword($_POST['contrasena']);
                $data['rol'] = 'Docente';
                
                $id = $this->model->create($data);
                $this->logger->info('Docente creado', ['id' => $id]);
                
                header('Location: /admin/docentes');
                exit;
            } catch (Exception $e) {
                require_once __DIR__ . '/../views/admin/docentes/create.php';
            }
        } else {
            require_once __DIR__ . '/../views/admin/docentes/create.php';
        }
    }
}