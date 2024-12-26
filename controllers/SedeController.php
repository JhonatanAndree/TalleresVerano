<?php
class SedeController {
    private $model;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new SedeModel($db);
        $this->logger = require_once __DIR__ . '/../includes/logger/ActivityLogger.php';
    }

    public function index() {
        $sedes = $this->model->getSedesActivas();
        require_once __DIR__ . '/../views/admin/sedes/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->validateSedeData($_POST);
                $id = $this->model->create($data);
                $this->logger->info('Sede creada', ['id' => $id]);
                
                header('Location: /admin/sedes');
                exit;
            } catch (Exception $e) {
                require_once __DIR__ . '/../views/admin/sedes/create.php';
            }
        } else {
            require_once __DIR__ . '/../views/admin/sedes/create.php';
        }
    }
}