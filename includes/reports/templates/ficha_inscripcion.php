<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .data-row {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            width: 200px;
            display: inline-block;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" class="logo" alt="Logo">
        <div class="title">Ficha de Inscripción - Talleres de Verano 2025</div>
        <div>Municipalidad Distrital de El Porvenir</div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Estudiante</div>
        <div class="data-row">
            <span class="label">Nombres y Apellidos:</span>
            <?php echo $estudiante_nombre . ' ' . $estudiante_apellido; ?>
        </div>
        <div class="data-row">
            <span class="label">DNI:</span>
            <?php echo $estudiante_dni; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Taller</div>
        <div class="data-row">
            <span class="label">Taller:</span>
            <?php echo $taller_nombre; ?>
        </div>
        <div class="data-row">
            <span class="label">Sede:</span>
            <?php echo $sede_nombre; ?>
        </div>
        <div class="data-row">
            <span class="label">Dirección:</span>
            <?php echo $sede_direccion; ?>
        </div>
        <div class="data-row">
            <span class="label">Horario:</span>
            <?php echo $hora_inicio . ' - ' . $hora_fin; ?>
        </div>
        <div class="data-row">
            <span class="label">Docente:</span>
            <?php echo $docente_nombre; ?>
        </div>
    </div>

    <div class="qr-code">
        <img src="<?php echo $qr_code; ?>" alt="QR Code">
    </div>

    <div class="footer">
        <div>Registrado por: <?php echo $registrador_nombre; ?></div>
        <div>Fecha de registro: <?php echo date('d/m/Y H:i'); ?></div>
        <div style="text-align: center; margin-top: 10px;">
            Este documento es una constancia oficial de inscripción
        </div>
    </div>
</body>
</html>