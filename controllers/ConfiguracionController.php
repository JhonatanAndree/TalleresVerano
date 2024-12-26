<?php
class ConfiguracionController {
    private $model;
    private $security;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new ConfiguracionModel($db);
        $this->security = SecurityHelper::getInstance();
        $this->logger = require_once __DIR__ . '/../includes/logger/ActivityLogger.php';
    }

    public function index() {
        if (!$this->security->checkPermission('configuracion', 'read')) {
            header('Location: /403');
            exit;
        }
        
        $config = $this->model->getConfiguracionActual();
        require_once __DIR__ . '/../views/admin/configuracion/index.php';
    }

    public function updateAnoFiscal() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
            $this->security->checkPermission('configuracion', 'update')) {
            try {
                $ano = filter_var($_POST['ano'], FILTER_SANITIZE_NUMBER_INT);
                
                if ($this->model->actualizarAnoFiscal($ano)) {
                    $this->logger->info('Año fiscal actualizado', ['ano' => $ano]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error al actualizar año fiscal']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}