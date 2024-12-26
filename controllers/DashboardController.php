<?php
/**
 * Controlador del Dashboard
 * Ruta: Controllers/DashboardController.php
 */

class DashboardController {
    private $model;
    private $permissions;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new DashboardModel($db);
        $this->permissions = PermissionHelper::getInstance();
    }

    public function index() {
        $this->permissions->requirePermission('dashboard', 'read');
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    public function getData() {
        $this->permissions->requirePermission('dashboard', 'read');
        
        $data = [
            'statistics' => $this->getStatistics(),
            'inscripciones' => $this->model->getInscripcionesPorTaller(),
            'ingresos' => $this->model->getIngresosMensuales(),
            'sedes' => $this->model->getDistribucionPorSede(),
            'asistencia' => $this->model->getAsistenciaSemanal()
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function getStatistics() {
        return [
            'totalEstudiantes' => $this->model->getTotalEstudiantes(),
            'totalIngresos' => $this->model->getTotalIngresos(),
            'talleresActivos' => $this->model->getTotalTalleresActivos(),
            'asistenciaPromedio' => $this->model->getAsistenciaPromedio()
        ];
    }
}