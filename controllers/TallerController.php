<?php
class TallerController {
    private $model;
    private $sedeModel;
    private $aulaModel;
    private $docenteModel;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new TallerModel($db);
        $this->sedeModel = new SedeModel($db);
        $this->aulaModel = new AulaModel($db);
        $this->docenteModel = new DocenteModel($db);
        $this->logger = require_once __DIR__ . '/../includes/logger/ActivityLogger.php';
    }

    public function index() {
        $idSede = filter_input(INPUT_GET, 'sede', FILTER_SANITIZE_NUMBER_INT);
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        
        $talleres = $idSede ? 
            $this->model->getTalleresDisponibles($idSede) : 
            $this->model->getAll([], $page);
            
        require_once __DIR__ . '/../views/admin/talleres/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = $this->validateTallerData($_POST);
                if (!$this->model->validarDisponibilidadHorario($data['id_horario'])) {
                    throw new Exception('Conflicto de horarios detectado');
                }
                
                $id = $this->model->create($data);
                $this->logger->info('Taller creado', ['id' => $id]);
                
                header('Location: /admin/talleres');
                exit;
            } catch (Exception $e) {
                require_once __DIR__ . '/../views/admin/talleres/create.php';
            }
        } else {
            require_once __DIR__ . '/../views/admin/talleres/create.php';
        }
    }

    private function validateTallerData($data) {
        return [
            'nombre' => filter_var($data['nombre'], FILTER_SANITIZE_STRING),
            'id_sede' => filter_var($data['id_sede'], FILTER_SANITIZE_NUMBER_INT),
            'id_aula' => filter_var($data['id_aula'], FILTER_SANITIZE_NUMBER_INT),
            'id_docente' => filter_var($data['id_docente'], FILTER_SANITIZE_NUMBER_INT),
            'capacidad_maxima' => filter_var($data['capacidad_maxima'], FILTER_SANITIZE_NUMBER_INT)
        ];
    }
}