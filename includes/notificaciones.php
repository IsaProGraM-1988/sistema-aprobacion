<?php
/**
 * Funciones de notificaciones del sistema
 */

function enviarNotificacionCorreo($destinatario, $asunto, $mensaje, $tipo = 'info') {
    $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #1a2a3a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8f9fa; }
            .footer { background: #1a2a3a; color: white; padding: 10px; text-align: center; font-size: 12px; }
            .btn { background: #2d6da8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Quillayes Surlat - Sistema de Aprobaciones</h2>
            </div>
            <div class='content'>
                <h3>$asunto</h3>
                <p>$mensaje</p>
            </div>
            <div class='footer'>
                <p>Sistema de Aprobaciones © " . date('Y') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarCorreo($destinatario, $asunto, $html, true);
}

function notificarVendedorSolicitud($email, $codigoSolicitud, $estado, $comentarios = '') {
    $estados = [
        'aprobada' => 'APROBADA ✅',
        'rechazada' => 'RECHAZADA ❌',
        'en_aprobacion' => 'EN APROBACIÓN ⏳'
    ];
    
    $estadoTexto = $estados[$estado] ?? strtoupper($estado);
    $icono = $estado === 'aprobada' ? '✅' : ($estado === 'rechazada' ? '❌' : '⏳');
    
    $asunto = "Solicitud $codigoSolicitud - $estadoTexto";
    
    $mensaje = "
    Estimado/a usuario,<br><br>
    
    Su solicitud <strong>$codigoSolicitud</strong> ha sido <strong>$estadoTexto</strong>.<br><br>
    ";
    
    if ($comentarios) {
        $mensaje .= "<strong>Comentarios del aprobador:</strong><br>
        <em>$comentarios</em><br><br>";
    }
    
    $mensaje .= "
    Puede revisar el detalle de su solicitud en el sistema.<br><br>
    
    <a href='" . URL_SISTEMA . "solicitudes/detalle' class='btn'>Ver Solicitud</a><br><br>
    
    Saludos,<br>
    <strong>Sistema de Aprobaciones Quillayes Surlat</strong>
    ";
    
    return enviarNotificacionCorreo($email, $asunto, $mensaje);
}

function notificarAprobadorNuevaSolicitud($email, $solicitud) {
    $asunto = "📋 Nueva solicitud pendiente de aprobación";
    
    $mensaje = "
    Estimado/a aprobador,<br><br>
    
    Se ha creado una nueva solicitud que requiere su aprobación:<br><br>
    
    <strong>Código:</strong> {$solicitud['codigo_solicitud']}<br>
    <strong>Cliente:</strong> {$solicitud['nombre_cliente']}<br>
    <strong>Vendedor:</strong> {$solicitud['email_vendedor']}<br>
    <strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($solicitud['fecha_creacion'])) . "<br><br>
    
    <a href='" . URL_SISTEMA . "aprobaciones/detalle/{$solicitud['id']}' class='btn'>Revisar Solicitud</a><br><br>
    
    Saludos,<br>
    <strong>Sistema de Aprobaciones Quillayes Surlat</strong>
    ";
    
    return enviarNotificacionCorreo($email, $asunto, $mensaje);
}

function notificarPrecioNoExiste($sku, $cliente, $vendedor) {
    $asunto = "⚠️ Precio no disponible - SKU $sku";
    
    $mensaje = "
    Estimado/a Jefe de Subcanal,<br><br>
    
    Se ha reportado que el SKU <strong>$sku</strong> no tiene precio asignado para el cliente <strong>{$cliente['nombre']}</strong>.<br><br>
    
    <strong>Detalles:</strong><br>
    - Cliente: {$cliente['nombre']} ({$cliente['codigo']})<br>
    - Subcanal: {$cliente['subcanal']}<br>
    - Vendedor: $vendedor<br><br>
    
    Por favor, gestione la creación del precio para este SKU en el sistema.<br><br>
    
    Saludos,<br>
    <strong>Sistema de Aprobaciones Quillayes Surlat</strong>
    ";
    
    $emailJefe = obtenerJefeSubcanal($cliente['subcanal']);
    
    if ($emailJefe) {
        return enviarNotificacionCorreo($emailJefe, $asunto, $mensaje);
    }
    
    return false;
}

function obtenerJefeSubcanal($subcanal) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT email FROM usuarios 
            WHERE rol = 'kam' AND subcanal = ? AND activo = 1 
            LIMIT 1
        ");
        $stmt->execute([$subcanal]);
        $result = $stmt->fetch();
        return $result['email'] ?? null;
    } catch (PDOException $e) {
        error_log("Error al obtener jefe de subcanal: " . $e->getMessage());
        return null;
    }
}

// Usar SMTP_USER 
function probarConfiguracionCorreo($emailPrueba = null) {
    if (!$emailPrueba) {
        $emailPrueba = SMTP_USER;
    }
    
    $asunto = "🔧 Prueba de configuración - Sistema Quillayes Surlat";
    $mensaje = "
    <h2>✅ ¡Configuración de correo exitosa!</h2>
    <p>Este es un correo de prueba enviado desde el Sistema de Aprobaciones.</p>
    <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
    <p><strong>Método:</strong> SMTP nativo (sin librerías externas)</p>
    ";
    
    return enviarCorreo($emailPrueba, $asunto, $mensaje, true);
}
?>