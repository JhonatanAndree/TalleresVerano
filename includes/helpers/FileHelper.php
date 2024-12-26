<?php
/**
 * Helper para manejo de archivos
 * Ruta: includes/helpers/FileHelper.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
use Dompdf\Dompdf;

class FileHelper {
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    private $maxFileSize = 5242880; // 5MB
    private $uploadPath;
    private static $instance = null;

    private function __construct() {
        $this->uploadPath = __DIR__ . '/../../public/uploads/';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function uploadFile($file, $folder = '') {
        try {
            $this->validateFile($file);
            
            $targetDir = $this->uploadPath . $folder;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $fileName = $this->generateFileName($file['name']);
            $targetFile = $targetDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                return [
                    'success' => true,
                    'path' => '/uploads/' . $folder . '/' . $fileName
                ];
            }

            throw new Exception('Error al mover el archivo.');
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function generatePDF($html, $options = []) {
        try {
            $dompdf = new Dompdf([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            $dompdf->loadHtml($html);
            $dompdf->setPaper($options['paper'] ?? 'A4', $options['orientation'] ?? 'portrait');
            $dompdf->render();

            if (isset($options['save_path'])) {
                file_put_contents($options['save_path'], $dompdf->output());
                return [
                    'success' => true,
                    'path' => $options['save_path']
                ];
            }

            return [
                'success' => true,
                'content' => $dompdf->output()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function generateCardId($estudianteData) {
        $html = $this->getCardIdTemplate($estudianteData);
        return $this->generatePDF($html, [
            'paper' => [0, 0, 241.89, 152.4], // Tamaño fotocheck horizontal (8.5cm x 5.4cm)
            'orientation' => 'landscape'
        ]);
    }

    public function generateFichaInscripcion($data) {
        $html = $this->getFichaTemplate($data);
        return $this->generatePDF($html, [
            'paper' => 'A4',
            'orientation' => 'portrait'
        ]);
    }

    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error en la carga del archivo.');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('El archivo excede el tamaño máximo permitido.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception('Tipo de archivo no permitido.');
        }
    }

    private function generateFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    private function getCardIdTemplate($data) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { margin: 0; padding: 10px; font-family: Arial, sans-serif; }
                .card { border: 1px solid #000; padding: 10px; }
                .header { text-align: center; font-weight: bold; margin-bottom: 10px; }
                .info { margin: 5px 0; }
                .qr { text-align: center; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="header">Talleres de Verano 2025 - MDEP</div>
                <div class="info">
                    <strong>Nombre:</strong> ' . htmlspecialchars($data['nombre']) . ' ' . htmlspecialchars($data['apellido']) . '<br>
                    <strong>DNI:</strong> ' . htmlspecialchars($data['dni']) . '<br>
                    <strong>Taller:</strong> ' . htmlspecialchars($data['taller']) . '<br>
                    <strong>Turno:</strong> ' . htmlspecialchars($data['turno']) . '<br>
                    <strong>Horario:</strong> ' . htmlspecialchars($data['horario']) . '<br>
                    <strong>Contacto:</strong> ' . htmlspecialchars($data['whatsapp']) . '<br>
                    <strong>Padre/Madre:</strong> ' . htmlspecialchars($data['padre_nombre']) . '
                </div>
                <div class="qr">
                    <img src="' . $this->generateQRCode($data['dni']) . '" width="100" height="100">
                </div>
            </div>
        </body>
        </html>';
    }

    private function getFichaTemplate($data) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
                .section { margin-bottom: 20px; }
                .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">Ficha de Inscripción - Talleres de Verano 2025</div>
                <div>Municipalidad Distrital de El Porvenir</div>
            </div>
            <!-- Contenido de la ficha -->
            <div class="section">
                <h3>Datos del Estudiante</h3>
                ' . $this->generateDataTable($data['estudiante']) . '
            </div>
            <div class="section">
                <h3>Datos del Padre/Madre</h3>
                ' . $this->generateDataTable($data['padre']) . '
            </div>
            <div class="section">
                <h3>Datos del Taller</h3>
                ' . $this->generateDataTable($data['taller']) . '
            </div>
            <div class="footer">
                <p>Registrado por: ' . htmlspecialchars($data['registrador']) . '<br>
                Fecha: ' . $data['fecha_registro'] . '</p>
            </div>
        </body>
        </html>';
    }

    private function generateDataTable($data) {
        $html = '<table style="width: 100%; border-collapse: collapse;">';
        foreach ($data as $key => $value) {
            $html .= '<tr>
                <td style="padding: 5px; border: 1px solid #ddd;"><strong>' . htmlspecialchars($key) . ':</strong></td>
                <td style="padding: 5px; border: 1px solid #ddd;">' . htmlspecialchars($value) . '</td>
            </tr>';
        }
        $html .= '</table>';
        return $html;
    }

    private function generateQRCode($data) {
        $qr = new \QRCode();
        return $qr->render($data);
    }
}