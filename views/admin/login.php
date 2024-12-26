<?php
require_once '../includes/functions.php';
session_start();
if(isset($_SESSION['user_id'])) header('Location: /dashboard.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Talleres de Verano</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/public/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Talleres de Verano</h3>
                        <form action="/controllers/auth_controller.php" method="POST">
                            <div class="form-group mb-3">
                                <input type="email" class="form-control" name="email" required placeholder="Correo electrónico">
                            </div>
                            <div class="form-group mb-3">
                                <input type="password" class="form-control" name="password" required placeholder="Contraseña">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Iniciar Sesión</button>
                        </form>
                        <div class="text-center">
                            <a href="/views/recovery.php">Recuperar contraseña</a>
                        </div>
                        <hr>
                        <a href="/views/consulta_padres.php" class="btn btn-secondary w-100">Acceso a Padres</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/public/js/bootstrap.bundle.min.js"></script>
</body>
</html>