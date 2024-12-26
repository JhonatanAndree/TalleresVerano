<?php
/**
 * Layout base del sistema
 * Ruta: views/layout/base.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php $this->yield('title'); ?> - Talleres de Verano</title>
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <?php $this->yield('styles'); ?>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
</head>
<body class="bg-gray-100 min-h-screen">
    <?php if (isset($_SESSION['user'])): ?>
        <?php $this->include('partials/navbar', ['user' => $_SESSION['user']]); ?>
        <?php $this->include('partials/sidebar', ['user' => $_SESSION['user']]); ?>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php if ($flash = Session::getInstance()->getFlash('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <?php echo $flash; ?>
        </div>
    <?php endif; ?>

    <?php if ($flash = Session::getInstance()->getFlash('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <?php echo $flash; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">
        <?php $this->yield('content'); ?>
    </main>

    <!-- Footer -->
    <?php $this->include('partials/footer'); ?>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <?php $this->yield('scripts'); ?>
</body>
</html>