<?php
// Configuración del sistema
date_default_timezone_set('America/Santiago');
setlocale(LC_TIME, 'es_ES.UTF-8');

// Configuración de moneda chilena
define('MONEDA', 'CLP');
define('SIMBOLO_MONEDA', '$');
define('DECIMALES', 0);
define('SEPARADOR_MILES', '.');
define('SEPARADOR_DECIMALES', ',');

// Rutas del sistema
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/sistema-aprobacion/');
}

// URL para redirecciones
if (!defined('URL_SISTEMA')) {
    define('URL_SISTEMA', BASE_URL . 'public/index.php?route=');
}

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

// ============================================================
// 📧 CONFIGURACIÓN DE CORREO
// ============================================================

// COMPLETA ESTOS DATOS CON TUS CREDENCIALES REALES
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' o 'ssl'

// ⚠️ IMPORTANTE: Usa una contraseña de aplicación de Gmail
// Cómo generar: https://myaccount.google.com/apppasswords
define('SMTP_USER', 'tucorreo@gmail.com');        // <-- CAMBIA ESTO
define('SMTP_PASS', 'tu_contraseña_app');          // <-- CAMBIA ESTO

define('SMTP_FROM', 'sistema@quillayessurlat.cl');
define('SMTP_FROM_NAME', 'Sistema de Aprobaciones Quillayes Surlat');

// Configuración de límites
define('MAX_SKUS', 30);
define('MAX_DIAS_PROMOCION', 365);

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SALT', 'qU1ll4y3s_SurL4t_2025');

// Mostrar errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>