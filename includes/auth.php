<?php
session_start();

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function checkPermission($requiredRole) {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit;
    }
    
    $userRole = getUserRole();
    $roles = [
        'SuperAdmin' => 4,
        'Administrador' => 3,
        'Docente' => 2,
        'Registrador' => 1
    ];
    
    return $roles[$userRole] >= $roles[$requiredRole];
}

function login($email, $password) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, contrasena, rol FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && verifyHash($password, $user['contrasena'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['rol'];
        createLog($user['id'], 'auth', 'Inicio de sesión');
        return true;
    }
    return false;
}

function logout() {
    if (isset($_SESSION['user_id'])) {
        createLog($_SESSION['user_id'], 'auth', 'Cierre de sesión');
    }
    session_destroy();
    header('Location: /login.php');
    exit;
}
?>