<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 10px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .card {
            width: 85.6mm;
            height: 54mm;
            border: 1px solid #000;
            padding: 10px;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
        .logo {
            max-width: 60px;
            margin-bottom: 5px;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .main-info {
            margin-bottom: 10px;
        }
        .data-row {
            margin: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
        }
        .value {
            font-size: 11px;
        }
        .qr-code {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 70px;
            height: 70px;
        }
        .footer {
            position: absolute;
            bottom: 5px;
            left: 10px;
            right: 10px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="logo.png" class="logo" alt="Logo">
            <div class="title">Talleres de Verano 2025 - MDEP</div>
        </div>

        <div class="main-info">
            <div class="data-row">
                <div class="label">ESTUDIANTE</div>
                <div class="value"><?php echo $nombre . ' ' . $apellido; ?></div>
            </div>
            <div class="data-row">
                <div class="label">DNI</div>
                <div class="value"><?php echo $dni; ?></div>
            </div>
            <div class="data-row">
                <div class="label">TALLER</div>
                <div class="value"><?php echo $taller_nombre; ?></div>
            </div>
            <div class="data-row">
                <div class="label">HORARIO</div>
                <div class="value"><?php echo $hora_inicio . ' - ' . $hora_fin; ?></div>
            </div>
            <div class="data-row">
                <div class="label">CONTACTO</div>
                <div class="value"><?php echo $contacto; ?></div>
            </div>
        </div>

        <img src="<?php echo $qr_code; ?>" class="qr-code" alt="QR Code">

        <div class="footer">
            Este documento es personal e intransferible
            Válido hasta: <?php echo date('d/m/Y', strtotime('+3 months')); ?>
        </div>
    </div>
</body>
</html>