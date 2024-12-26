<?php
/**
 * Header principal del sistema
 * Ruta: views/layout/header.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Talleres de Verano MDEP</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="/public/css/styles.css" rel="stylesheet">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-100">
    <?php if (isset($_SESSION['user'])): ?>
        <?php require_once __DIR__ . '/navbar.php'; ?>
        <?php require_once __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>
    <div class="main-content">