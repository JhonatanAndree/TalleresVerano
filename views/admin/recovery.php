<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Contraseña</title>
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
                        <h3 class="text-center mb-4">Recuperar Contraseña</h3>
                        <form action="/controllers/recovery_controller.php" method="POST">
                            <div class="form-group mb-3">
                                <input type="email" class="form-control" name="email" required placeholder="Correo electrónico registrado">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Enviar Instrucciones</button>
                        </form>
                        <div class="text-center">
                            <a href="/views/login.php">Volver al login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>