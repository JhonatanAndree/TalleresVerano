<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $db->prepare("UPDATE usuarios SET reset_token = ?, reset_expiry = ? WHERE email = ?");
            $stmt->execute([$token, $expiry, $email]);
            
            // Aquí iría el código para enviar el email
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/views/reset_password.php?token=" . $token;
            
            header('Location: /views/login.php?msg=recovery_sent');
        } else {
            header('Location: /views/recovery.php?error=email_not_found');
        }
    } catch(Exception $e) {
        header('Location: /views/recovery.php?error=system_error');
    }
}