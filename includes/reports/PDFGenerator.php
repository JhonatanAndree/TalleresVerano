<?php
class PDFGenerator {
    private $dompdf;
    private $logger;
    private $options;

    public function __construct() {
        $this->logger = ActivityLogger::getInstance();
        $this->options = new Options();
        $this->options->set('isHtml5ParserEnabled', true);
        $this->options->set('isPhpEnabled', true);
        $this->dompdf = new Dompdf($this->options);
    }

    public function generateFichaInscripcion($matriculaId) {
        try {
            $data = $this->obtenerDatosMatricula($matriculaId);
            $html = $this->renderTemplate('ficha_inscripcion', $data);
            return $this->generatePDF($html, 'Ficha_Inscripcion.pdf');
        } catch (Exception $e) {
            $this->logger->error('Error generando ficha de inscripción', [
                'error' => $e->getMessage(),
                'matricula_id' => $matriculaId
            ]);
            throw $e;
        }
    }

    public function generateCardID($estudianteId) {
        try {
            $data = $this->obtenerDatosEstudiante($estudianteId);
            $html = $this->renderTemplate('card_id', $data);
            return $this->generatePDF($html, 'Card_ID.pdf', [
                'paper' => 'card',
                'orientation' => 'landscape'
            ]);
        } catch (Exception $e) {
            $this->logger->error('Error generando Card ID', [
                'error' => $e->getMessage(),
                'estudiante_id' => $estudianteId
            ]);
            throw $e;
        }
    }

    public function generateReporteAsistencia($filtros) {
        try {
            $data = $this->obtenerDatosAsistencia($filtros);
            $html = $this->renderTemplate('reporte_asistencia', $data);
            return $this->generatePDF($html, 'Reporte_Asistencia.pdf');
        } catch (Exception $e) {
            $this->logger->error('Error generando reporte de asistencia', [
                'error' => $e->getMessage(),
                'filtros' => $filtros
            ]);
            throw $e;
        }
    }

    private function generatePDF($html, $filename, $options = []) {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper(
            $options['paper'] ?? 'A4',
            $options['orientation'] ?? 'portrait'
        );
        $this->dompdf->render();

        if (isset($options['save_path'])) {
            file_put_contents($options['save_path'], $this->dompdf->output());
            return $options['save_path'];
        }

        return $this->dompdf->output();
    }

    private function renderTemplate($template, $data) {
        ob_start();
        extract($data);
        include __DIR__ . "/templates/{$template}.php";
        return ob_get_clean();
    }

    private function obtenerDatosMatricula($matriculaId) {
        $sql = "SELECT m.*, 
                       e.nombre as estudiante_nombre,
                       e.apellido as estudiante_apellido,
                       e.dni as estudiante_dni,
                       t.nombre as taller_nombre,
                       h.hora_inicio,
                       h.hora_fin,
                       s.nombre as sede_nombre,
                       s.direccion as sede_direccion,
                       d.nombre as docente_nombre,
                       r.nombre as registrador_nombre
                FROM matriculas m
                JOIN estudiantes e ON m.estudiante_id = e.id
                JOIN talleres t ON m.taller_id = t.id
                JOIN horarios h ON m.horario_id = h.id
                JOIN sedes s ON t.sede_id = s.id
                JOIN usuarios d ON t.docente_id = d.id
                JOIN usuarios r ON m.registrador_id = r.id
                WHERE m.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matriculaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenerDatosEstudiante($estudianteId) {
        $sql = "SELECT e.*,
                       t.nombre as taller_nombre,
                       h.hora_inicio,
                       h.hora_fin,
                       p.telefono as contacto
                FROM estudiantes e
                JOIN matriculas m ON e.id = m.estudiante_id
                JOIN talleres t ON m.taller_id = t.id
                JOIN horarios h ON m.horario_id = h.id
                JOIN usuarios p ON e.padre_id = p.id
                WHERE e.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estudianteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenerDatosAsistencia($filtros) {
        $sql = "SELECT a.*,
                       e.nombre as estudiante_nombre,
                       e.apellido as estudiante_apellido,
                       t.nombre as taller_nombre
                FROM asistencias a
                JOIN estudiantes e ON a.estudiante_id = e.id
                JOIN talleres t ON a.taller_id = t.id
                WHERE 1=1";

        $params = [];
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND a.fecha >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND a.fecha <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['taller_id'])) {
            $sql .= " AND a.taller_id = ?";
            $params[] = $filtros['taller_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return [
            'asistencias' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'filtros' => $filtros
        ];
    }
}