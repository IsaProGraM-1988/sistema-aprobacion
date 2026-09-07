<?php
/**
 * Funciones auxiliares del sistema
 */

// ============================================================
// 📧 ENVÍO DE CORREOS
// ============================================================

/**
 * Enviar correo usando SMTP con socket nativo (sin librerías externas)
 * Compatible con Gmail, Outlook, etc.
 */
function enviarCorreoSMTP($destinatario, $asunto, $mensaje, $html = true) {
    
    // Configuración SMTP (definida en config.php)
    $smtpConfig = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USER,
        'password' => SMTP_PASS,
        'secure' => SMTP_SECURE, // 'tls' o 'ssl'
        'from' => SMTP_FROM,
        'from_name' => SMTP_FROM_NAME
    ];
    
    // Si no hay credenciales configuradas, usar mail() nativo
    if (empty($smtpConfig['username']) || $smtpConfig['username'] === 'tucorreo@gmail.com') {
        return enviarCorreoNat($destinatario, $asunto, $mensaje, $html);
    }
    
    // Construir el mensaje
    $boundary = md5(uniqid());
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: " . $smtpConfig['from_name'] . " <" . $smtpConfig['from'] . ">\r\n";
    $headers .= "Reply-To: " . $smtpConfig['from'] . "\r\n";
    $headers .= "Content-Type: " . ($html ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    
    $body = $mensaje;
    
    // Conectar al servidor SMTP
    $host = ($smtpConfig['secure'] === 'ssl') ? 'ssl://' . $smtpConfig['host'] : $smtpConfig['host'];
    $port = $smtpConfig['port'];
    
    $socket = @fsockopen($host, $port, $errno, $errstr, 30);
    if (!$socket) {
        error_log("Error SMTP: No se pudo conectar a $host:$port - $errstr ($errno)");
        return enviarCorreoNat($destinatario, $asunto, $mensaje, $html);
    }
    
    // Leer respuesta inicial
    $response = fgets($socket, 515);
    
    // Comandos SMTP
    $commands = [
        "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n",
        "STARTTLS\r\n",
        "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n",
        "AUTH LOGIN\r\n",
        base64_encode($smtpConfig['username']) . "\r\n",
        base64_encode($smtpConfig['password']) . "\r\n",
        "MAIL FROM: <" . $smtpConfig['from'] . ">\r\n",
        "RCPT TO: <" . $destinatario . ">\r\n",
        "DATA\r\n"
    ];
    
    // Enviar comandos
    foreach ($commands as $cmd) {
        fputs($socket, $cmd);
        // Leer respuesta (excepto para DATA)
        if ($cmd !== "DATA\r\n") {
            $response = fgets($socket, 515);
            // Si la respuesta no es 2xx o 3xx, algo falló
            if (substr($response, 0, 1) !== '2' && substr($response, 0, 1) !== '3') {
                error_log("Error SMTP: $response");
                fclose($socket);
                return enviarCorreoNat($destinatario, $asunto, $mensaje, $html);
            }
        }
    }
    
    // Enviar el contenido del correo
    $message = "Subject: " . $asunto . "\r\n";
    $message .= $headers . "\r\n";
    $message .= $body . "\r\n";
    $message .= "\r\n.\r\n";
    
    fputs($socket, $message);
    $response = fgets($socket, 515);
    
    // Cerrar conexión
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    // Verificar si fue exitoso
    if (substr($response, 0, 1) === '2') {
        return true;
    }
    
    error_log("Error SMTP al enviar: $response");
    return false;
}

/**
 * Función de respaldo: enviar correo con mail() nativo
 */
function enviarCorreoNat($destinatario, $asunto, $mensaje, $html = false) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/" . ($html ? 'html' : 'plain') . "; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return @mail($destinatario, $asunto, $mensaje, $headers);
}

/**
 * Función principal de envío (elige automáticamente el método)
 */
function enviarCorreo($destinatario, $asunto, $mensaje, $html = true) {
    // Intentar primero con SMTP si está configurado
    if (defined('SMTP_USER') && SMTP_USER && SMTP_USER !== 'tucorreo@gmail.com') {
        return enviarCorreoSMTP($destinatario, $asunto, $mensaje, $html);
    }
    
    // Fallback: mail() nativo
    return enviarCorreoNat($destinatario, $asunto, $mensaje, $html);
}


// Formatear moneda chilena
function formatearMoneda($monto) {
    return '$' . number_format($monto, 0, ',', '.');
}

// Formatear número con separador de miles
function formatearNumero($numero, $decimales = 0) {
    return number_format($numero, $decimales, ',', '.');
}

// Obtener nombre de mes en español
function nombreMes($mes) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $meses[(int)$mes] ?? '';
}

// Obtener nombre del día en español
function nombreDia($dia) {
    $dias = [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
    ];
    return $dias[(int)$dia] ?? '';
}

// Generar código único
function generarCodigo($prefijo = '') {
    return $prefijo . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Validar RUT chileno
function validarRut($rut) {
    $rut = preg_replace('/[^0-9kK]/', '', $rut);
    if (strlen($rut) < 2) return false;
    
    $dv = substr($rut, -1);
    $numero = substr($rut, 0, -1);
    
    $suma = 0;
    $multiplicador = 2;
    
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += intval($numero[$i]) * $multiplicador;
        $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
    }
    
    $dvEsperado = 11 - ($suma % 11);
    $dvEsperado = $dvEsperado === 11 ? 0 : ($dvEsperado === 10 ? 'K' : $dvEsperado);
    
    return strtoupper($dv) === strtoupper($dvEsperado);
}

// Limpiar texto para evitar XSS
function limpiarTexto($texto) {
    return htmlspecialchars(strip_tags($texto), ENT_QUOTES, 'UTF-8');
}

// Sanitizar entrada
function sanitizar($input) {
    if (is_array($input)) {
        return array_map('sanitizar', $input);
    }
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

// Obtener IP del usuario
function obtenerIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip;
}

// Registrar actividad en log
function registrarActividad($usuario, $accion, $detalles = null) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO logs_sistema (email_usuario, accion, detalles, ip, fecha)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $usuario,
            $accion,
            $detalles,
            obtenerIP()
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

// Verificar permisos
function tienePermiso($rolRequerido) {
    if (!Session::isLoggedIn()) return false;
    
    $rolUsuario = Session::get('rol');
    $rolesPermitidos = is_array($rolRequerido) ? $rolRequerido : [$rolRequerido];
    
    return in_array($rolUsuario, $rolesPermitidos);
}

// Obtener estado de solicitud en español
function estadoSolicitud($estado) {
    $estados = [
        'borrador' => 'Borrador',
        'enviada' => 'Enviada',
        'en_aprobacion' => 'En Aprobación',
        'aprobada' => 'Aprobada',
        'aprobada_parcial' => 'Aprobada Parcialmente',
        'rechazada' => 'Rechazada'
    ];
    return $estados[$estado] ?? $estado;
}

// Obtener clase de estado
function estadoClase($estado) {
    $clases = [
        'borrador' => 'secondary',
        'enviada' => 'info',
        'en_aprobacion' => 'warning',
        'aprobada' => 'success',
        'aprobada_parcial' => 'warning',
        'rechazada' => 'danger'
    ];
    return $clases[$estado] ?? 'secondary';
}

// Calcular diferencia de días entre fechas
function diasEntreFechas($fecha1, $fecha2) {
    $date1 = new DateTime($fecha1);
    $date2 = new DateTime($fecha2);
    return $date1->diff($date2)->days;
}

// Validar fecha no sea anterior a hoy
function fechaValida($fecha) {
    $fechaObj = new DateTime($fecha);
    $hoy = new DateTime();
    $hoy->setTime(0, 0, 0);
    return $fechaObj >= $hoy;
}
?>