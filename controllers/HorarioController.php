<?php
class HorarioController {
    private $model;
    private $tallerModel;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new HorarioModel($db);
        $this->tallerModel = new TallerModel($db);
        $this->logger = require_once __DIR__ . '/../includes/logger/ActivityLogger.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->validateHorarioData($_POST);
                
                if (!$this->model->validarConflictos(
                    $data['id_taller'], 
                    $data['intervalo_tiempo'], 
                    $data['turno']
                )) {
                    throw new Exception('Conflicto de horarios detectado');
                }
                
                $id = $this->model->create($data);
                $this->logger->info('Horario creado', ['id' => $id]);
                
                header('Location: /admin/horarios');
                exit;
            } catch (Exception $e) {
                require_once __DIR__ . '/../views/admin/horarios/create.php';
            }
        } else {
            require_once __DIR__ . '/../views/admin/horarios/create.php';
        }
    }
}