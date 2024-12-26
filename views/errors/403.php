<?php
$page_title = 'Acceso Denegado';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">403</h1>
            <h2>Acceso Denegado</h2>
            <p class="lead">No tiene permisos para acceder a este recurso.</p>
            <a href="/dashboard" class="btn btn-primary">Volver al inicio</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>