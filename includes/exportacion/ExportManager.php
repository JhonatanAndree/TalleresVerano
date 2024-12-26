<?php
class ExportManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function exportarCSV($tipo, $filtros = []) {
        $data = $this->obtenerDatos($tipo, $filtros);
        $filename = $tipo . '_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($data[0] ?? []));
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }

    public function exportarExcel($tipo, $filtros = []) {
        require_once ROOT_PATH . '/vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $data = $this->obtenerDatos($tipo, $filtros);
        
        if (!empty($data)) {
            $sheet->fromArray([array_keys($data[0])], null, 'A1');
            $sheet->fromArray($data, null, 'A2');
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $tipo . '_' . date('Y-m-d_His') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
    }

    private function obtenerDatos($tipo, $filtros) {
        switch($tipo) {
            case 'estudiantes':
                return $this->obtenerDatosEstudiantes($filtros);
            case 'pagos':
                return $this->obtenerDatosPagos($filtros);
            case 'asistencias':
                return $this->obtenerDatosAsistencias($filtros);
            default:
                throw new Exception('Tipo de exportación no válido');
        }
    }

    private function obtenerDatosEstudiantes($filtros) {
        $sql = "SELECT e.*, t.nombre as taller, s.nombre as sede
                FROM estudiantes e
                JOIN talleres t ON e.id_taller = t.id
                JOIN sedes s ON t.id_sede = s.id
                WHERE e.activo = 1";
        
        if (!empty($filtros['sede_id'])) {
            $sql .= " AND s.id = " . intval($filtros['sede_id']);
        }
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}