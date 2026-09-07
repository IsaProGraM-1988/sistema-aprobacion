<?php
/**
 * Funciones de seguridad del sistema
 */

// Prevenir XSS
function prevenirXSS($input) {
    if (is_array($input)) {
        return array_map('prevenirXSS', $input);
    }
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Validar email corporativo
function validarEmailCorporativo($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           (strpos($email, '@quillayessurlat.cl') !== false);
}

// Sanitizar URL
function sanitizarURL($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

// Limitar intentos de login 
function verificarIntentosLogin($email) {
    $maxIntentos = 5;
    $tiempoBloqueo = 900; // 15 minutos en segundos
    
    if (!isset($_SESSION['login_intentos'])) {
        $_SESSION['login_intentos'] = [];
    }
    
    $intentos = $_SESSION['login_intentos'];
    $ahora = time();
    
    // Limpiar intentos viejos
    foreach ($intentos as $key => $timestamp) {
        if ($ahora - $timestamp > $tiempoBloqueo) {
            unset($intentos[$key]);
        }
    }
    
    // Verificar si el email está bloqueado
    if (isset($intentos[$email]) && count($intentos[$email]) >= $maxIntentos) {
        return false;
    }
    
    return true;
}

// Registrar intento de login fallido
function registrarIntentoFallido($email) {
    if (!isset($_SESSION['login_intentos'][$email])) {
        $_SESSION['login_intentos'][$email] = [];
    }
    $_SESSION['login_intentos'][$email][] = time();
}

// Generar password seguro
function generarPasswordSeguro($longitud = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Hashear contraseña
function hashearPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

// Verificar contraseña
function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>