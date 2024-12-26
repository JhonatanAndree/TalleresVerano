<?php
require_once '../includes/constants.php';
require_once '../includes/auth.php';
require_once 'documento_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $dni = sanitizeInput($_POST['dni']);
    
    $docController = new DocumentoController();
    
    switch($tipo) {
        case 'ficha':
            $docController->generarFichaInscripcion($dni);
            break;
        case 'card':
            $docController->generarCardID($dni);
            break;
    }
}
?>