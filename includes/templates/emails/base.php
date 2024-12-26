<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $subject; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px;
            background: #4f46e5;
            color: white;
        }
        .content {
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo getenv('APP_URL'); ?>/img/logo.png" alt="Logo" height="50">
            <h1><?php echo $title ?? $subject; ?></h1>
        </div>
        
        <div class="content">
            <?php echo $content; ?>
        </div>

        <div class="footer">
            <p>Municipalidad Distrital de El Porvenir<br>
            Talleres de Verano 2025<br>
            <?php echo getenv('CONTACT_PHONE'); ?><br>
            <?php echo getenv('CONTACT_EMAIL'); ?></p>
        </div>
    </div>
</body>
</html>