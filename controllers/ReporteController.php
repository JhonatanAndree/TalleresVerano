<?php
/**
 * Controlador de Reportes
 * Ruta: Controllers/ReporteController.php
 */

require_once __DIR__ . '/../includes/helpers/FileHelper.php';
require_once __DIR__ . '/../Models/ReporteModel.php';

class ReporteController {
    private $model;
    private $fileHelper;
    private $logger;

    public function __construct() {
        $db = require_once __DIR__ . '/../Config/db.php';
        $this->model = new ReporteModel($db);
        $this->fileHelper = FileHelper::getInstance();
        $this->logger = ActivityLogger::getInstance();
    }

    public function generate() {
        try {
            $filters = $this->validateFilters($_GET);
            $data = $this->model->getReportData($filters);
            $charts = $this->generateCharts($data);

            return json_encode([
                'success' => true,
                'report' => $data,
                'charts' => $charts
            ]);
        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function export() {
        try {
            $filters = $this->validateFilters($_GET);
            $format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING) ?? 'pdf';
            $data = $this->model->getReportData($filters);

            switch ($format) {
                case 'pdf':
                    return $this->exportPDF($data, $filters);
                case 'excel':
                    return $this->exportExcel($data, $filters);
                case 'csv':
                    return $this->exportCSV($data, $filters);
                default:
                    throw new Exception('Formato no soportado');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function validateFilters($params) {
        return [
            'fecha_inicio' => filter_var($params['fecha_inicio'] ?? null, FILTER_SANITIZE_STRING),
            'fecha_fin' => filter_var($params['fecha_fin'] ?? null, FILTER_SANITIZE_STRING),
            'tipo' => filter_var($params['tipo'] ?? null, FILTER_SANITIZE_STRING),
            'sede' => filter_var($params['sede'] ?? null, FILTER_SANITIZE_NUMBER_INT),
            'taller' => filter_var($params['taller'] ?? null, FILTER_SANITIZE_NUMBER_INT),
            'page' => filter_var($params['page'] ?? 1, FILTER_SANITIZE_NUMBER_INT),
            'per_page' => filter_var($params['per_page'] ?? 25, FILTER_SANITIZE_NUMBER_INT)
        ];
    }

    private function generateCharts($data) {
        $charts = [];

        // Gráfico de estudiantes por taller
        if (isset($data['estudiantes_por_taller'])) {
            $charts['estudiantesPorTaller'] = [
                'type' => 'bar',
                'data' => [
                    'labels' => array_column($data['estudiantes_por_taller'], 'taller'),
                    'datasets' => [[
                        'label' => 'Estudiantes por Taller',
                        'data' => array_column($data['estudiantes_por_taller'], 'total'),
                        'backgroundColor' => '#4F46E5'
                    ]]
                ],
                'options' => [
                    'responsive' => true,
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true
                        ]
                    ]
                ]
            ];
        }

        // Gráfico de ingresos mensuales
        if (isset($data['ingresos_mensuales'])) {
            $charts['ingresosMensuales'] = [
                'type' => 'line',
                'data' => [
                    'labels' => array_column($data['ingresos_mensuales'], 'mes'),
                    'datasets' => [[
                        'label' => 'Ingresos Mensuales',
                        'data' => array_column($data['ingresos_mensuales'], 'total'),
                        'borderColor' => '#10B981',
                        'fill' => false
                    ]]
                ],
                'options' => [
                    'responsive' => true
                ]
            ];
        }

        return $charts;
    }

    private function exportPDF($data, $filters) {
        $template = new ReportTemplate($data, [
            'title' => 'Reporte de Talleres',
            'subtitle' => $this->getReportSubtitle($filters)
        ]);

        $result = $this->fileHelper->generatePDF($template->render(), [
            'paper' => 'A4',
            'orientation' => 'portrait'
        ]);

        if ($result['success']) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reporte.pdf"');
            echo $result['content'];
        } else {
            throw new Exception('Error generando PDF');
        }
    }

    private function exportExcel($data, $filters) {
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = array_keys($data['rows'][0] ?? []);
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $this->formatHeader($header));
        }

        // Data
        foreach ($data['rows'] as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowIndex + 2,
                    $this->formatExcelCell($value)
                );
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="reporte.xlsx"');
        
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    private function exportCSV($data, $filters) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reporte.csv"');

        $output = fopen('php://output', 'w');
        
        // Headers
        if (!empty($data['rows'])) {
            fputcsv($output, array_map([$this, 'formatHeader'], array_keys($data['rows'][0])));
        }

        // Data
        foreach ($data['rows'] as $row) {
            fputcsv($output, array_map([$this, 'formatCSVCell'], $row));
        }

        fclose($output);
    }

    private function formatHeader($header) {
        return ucwords(str_replace('_', ' ', $header));
    }

    private function formatExcelCell($value) {
        if ($value === null) return '';
        if (is_bool($value)) return $value ? 'Sí' : 'No';
        return $value;
    }

    private function formatCSVCell($value) {
        if ($value === null) return '';
        if (is_bool($value)) return $value ? 'Sí' : 'No';
        if (is_numeric($value)) return $value;
        return $value;
    }

    private function getReportSubtitle($filters) {
        $parts = [];

        if ($filters['fecha_inicio']) {
            $parts[] = "Desde: " . date('d/m/Y', strtotime($filters['fecha_inicio']));
        }
        if ($filters['fecha_fin']) {
            $parts[] = "Hasta: " . date('d/m/Y', strtotime($filters['fecha_fin']));
        }
        if ($filters['tipo']) {
            $parts[] = "Tipo: " . ucfirst($filters['tipo']);
        }
        if ($filters['sede']) {
            $sede = $this->model->getSedeNombre($filters['sede']);
            if ($sede) {
                $parts[] = "Sede: " . $sede;
            }
        }
        if ($filters['taller']) {
            $taller = $this->model->getTallerNombre($filters['taller']);
            if ($taller) {
                $parts[] = "Taller: " . $taller;
            }
        }

        return implode(' | ', $parts);
    }

    public function dashboard() {
        $anoFiscal = date('Y');
        $data = [
            'total_estudiantes' => $this->model->getTotalEstudiantes($anoFiscal),
            'total_ingresos' => $this->model->getTotalIngresos($anoFiscal),
            'talleres_populares' => $this->model->getTalleresPopulares($anoFiscal),
            'estadisticas_mensuales' => $this->model->getEstadisticasMensuales($anoFiscal)
        ];

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    public function reporteIngresos() {
        if (!$this->checkPermissions('reportes', 'ingresos')) {
            header('Location: /403');
            exit;
        }

        $filters = $this->validateFilters($_GET);
        $data = $this->model->getReporteIngresos($filters);
        
        if (isset($_GET['export'])) {
            return $this->export();
        }

        require_once __DIR__ . '/../views/admin/reportes/ingresos.php';
    }

    public function reporteAsistencia() {
        if (!$this->checkPermissions('reportes', 'asistencia')) {
            header('Location: /403');
            exit;
        }

        $filters = $this->validateFilters($_GET);
        $data = $this->model->getReporteAsistencia($filters);
        
        if (isset($_GET['export'])) {
            return $this->export();
        }

        require_once __DIR__ . '/../views/admin/reportes/asistencia.php';
    }

    private function checkPermissions($modulo, $accion) {
        return isset($_SESSION['user']) && 
               ($_SESSION['user']['rol'] === 'SuperAdmin' || 
                $_SESSION['user']['rol'] === 'Administrador');
    }
}