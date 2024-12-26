<?php
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class DocumentoController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function generarFichaInscripcion($dni_estudiante) {
        $estudiante = $this->obtenerDatosEstudiante($dni_estudiante);
        
        $html = $this->generarHTMLFicha($estudiante);
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="ficha_inscripcion.pdf"');
        echo $dompdf->output();
    }

    public function generarCardID($dni_estudiante) {
        $estudiante = $this->obtenerDatosEstudiante($dni_estudiante);
        
        $qr = $this->generarQR("/perfil_estudiante.php?dni=" . $dni_estudiante);
        
        $html = $this->generarHTMLCard($estudiante, $qr);
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 241.89, 152.4]); // Tamaño fotocheck
        $dompdf->render();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="card_id.pdf"');
        echo $dompdf->output();
    }

    private function generarQR($data) {
        $qr = QrCode::create($data);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        return $result->getDataUri();
    }

    private function obtenerDatosEstudiante($dni) {
        $stmt = $this->db->prepare("
            SELECT e.*, t.nombre as taller_nombre, s.nombre as sede_nombre,
                   d.nombre as docente_nombre, h.intervalo_tiempo,
                   p.nombre as padre_nombre, p.apellido as padre_apellido,
                   p.telefono as padre_telefono, r.nombre as registrador_nombre
            FROM estudiantes e
            JOIN talleres t ON e.id_taller = t.id
            JOIN sedes s ON t.id_sede = s.id
            JOIN usuarios d ON t.id_docente = d.id
            JOIN usuarios p ON e.id_padre = p.id
            JOIN horarios h ON t.id = h.id_taller
            JOIN usuarios r ON e.registrador_id = r.id
            WHERE e.dni = ?
        ");
        $stmt->execute([$dni]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarHTMLFicha($estudiante) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial; }
                .header { text-align: center; margin-bottom: 20px; }
                .content { margin: 20px; }
                .footer { position: fixed; bottom: 0; width: 100%; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Ficha de Inscripción - Talleres de Verano 2025</h2>
                <h3>Municipalidad Distrital de El Porvenir</h3>
            </div>
            <div class="content">
                <p><strong>Estudiante:</strong> ' . $estudiante['nombre'] . ' ' . $estudiante['apellido'] . '</p>
                <p><strong>DNI:</strong> ' . $estudiante['dni'] . '</p>
                <p><strong>Taller:</strong> ' . $estudiante['taller_nombre'] . '</p>
                <p><strong>Sede:</strong> ' . $estudiante['sede_nombre'] . '</p>
                <p><strong>Horario:</strong> ' . $estudiante['intervalo_tiempo'] . '</p>
                <p><strong>Docente:</strong> ' . $estudiante['docente_nombre'] . '</p>
                <p><strong>Padre/Madre:</strong> ' . $estudiante['padre_nombre'] . ' ' . $estudiante['padre_apellido'] . '</p>
                <p><strong>Teléfono:</strong> ' . $estudiante['padre_telefono'] . '</p>
            </div>
            <div class="footer">
                <p>Registrado por: ' . $estudiante['registrador_nombre'] . '</p>
                <p>Fecha: ' . date('d/m/Y') . '</p>
            </div>
        </body>
        </html>';
    }

    private function generarHTMLCard($estudiante, $qrCode) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial; margin: 0; }
                .card { width: 241.89px; height: 152.4px; position: relative; }
                .header { background: #1a237e; color: white; padding: 5px; text-align: center; }
                .content { padding: 5px; font-size: 10px; }
                .qr { position: absolute; right: 5px; top: 30px; width: 50px; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="header">
                    <h3>Talleres de Verano 2025 - MDEP</h3>
                </div>
                <div class="content">
                    <p><strong>' . $estudiante['nombre'] . ' ' . $estudiante['apellido'] . '</strong></p>
                    <p>DNI: ' . $estudiante['dni'] . '</p>
                    <p>Taller: ' . $estudiante['taller_nombre'] . '</p>
                    <p>Turno/Horario: ' . $estudiante['intervalo_tiempo'] . '</p>
                    <p>Contacto: ' . $estudiante['padre_telefono'] . '</p>
                    <p>Padre/Madre: ' . $estudiante['padre_nombre'] . ' ' . $estudiante['padre_apellido'] . '</p>
                    <img src="' . $qrCode . '" class="qr">
                </div>
            </div>
        </body>
        </html>';
    }
}