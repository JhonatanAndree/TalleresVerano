<?php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateHash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyHash($password, $hash) {
    return password_verify($password, $hash);
}

function createLog($userId, $action, $details) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO historial_cambios (usuario_id, modulo_afectado, descripcion_cambio) VALUES (?, ?, ?)");
    return $stmt->execute([$userId, $action, $details]);
}
?>