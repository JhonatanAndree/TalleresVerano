<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni_estudiante = sanitizeInput($_POST['dni_estudiante']);
    $dni_padre = sanitizeInput($_POST['dni_padre']);
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT e.*, t.nombre as taller_nombre, s.nombre as sede_nombre,
                   d.nombre as docente_nombre, h.intervalo_tiempo
            FROM estudiantes e
            JOIN talleres t ON e.id_taller = t.id
            JOIN sedes s ON t.id_sede = s.id
            JOIN usuarios d ON t.id_docente = d.id
            JOIN horarios h ON t.id = h.id_taller
            WHERE e.dni = ? AND EXISTS (
                SELECT 1 FROM usuarios u 
                WHERE u.id = e.id_padre AND u.dni = ?
            )
        ");
        
        $stmt->execute([$dni_estudiante, $dni_padre]);
        
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                'success' => true,
                'estudiante' => [
                    'nombre' => $result['nombre'],
                    'apellido' => $result['apellido'],
                    'dni' => $result['dni']
                ],
                'taller' => [
                    'nombre' => $result['taller_nombre'],
                    'sede' => $result['sede_nombre'],
                    'docente' => $result['docente_nombre'],
                    'horario' => $result['intervalo_tiempo']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontraron datos con los DNI proporcionados'
            ]);
        }
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al procesar la consulta'
        ]);
    }
}
?>