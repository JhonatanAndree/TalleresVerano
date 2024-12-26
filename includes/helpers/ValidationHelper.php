<?php
class ValidationHelper {
    public static function validarDNI($dni) {
        return preg_match('/^[0-9]{8}$/', $dni);
    }

    public static function validarCelular($numero) {
        return preg_match('/^9[0-9]{8}$/', $numero);
    }

    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validarHorario($hora_inicio, $hora_fin) {
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio) ||
            !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
            return false;
        }
        return strtotime($hora_inicio) < strtotime($hora_fin);
    }

    public static function sanitizarInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizarInput'], $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}