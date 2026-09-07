<?php
// Asegurar que las constantes estén cargadas
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
    require_once BASE_PATH . '/config/config.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - Sistema de Aprobaciones Quillayes Surlat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2a3a;
            --secondary: #2d6da8;
            --accent: #e88d1d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f0f2f5;
            padding: 20px;
        }
        
        .manual-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        /* HEADER */
        .manual-header {
            background: linear-gradient(135deg, #1a2a3a 0%, #2d6da8 100%);
            color: white;
            padding: 2.5rem 3rem;
            text-align: center;
        }
        
        .manual-header img {
            max-height: 80px;
            margin-bottom: 1rem;
        }
        
        .manual-header h1 {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 0.3rem;
        }
        
        .manual-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
        }
        
        .manual-header .version {
            background: rgba(255,255,255,0.15);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        /* BARRA LATERAL DE NAVEGACIÓN */
        .manual-nav {
            background: var(--light);
            padding: 1.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .manual-nav .nav-link {
            color: var(--primary);
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .manual-nav .nav-link:hover {
            background: var(--secondary);
            color: white;
        }
        
        .manual-nav .nav-link i {
            width: 24px;
        }
        
        /* CONTENIDO */
        .manual-body {
            padding: 2.5rem 3rem;
        }
        
        .section {
            margin-bottom: 3rem;
            scroll-margin-top: 80px;
        }
        
        .section h2 {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.8rem;
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 0.8rem;
            margin-bottom: 1.5rem;
        }
        
        .section h2 i {
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .section h3 {
            color: var(--secondary);
            font-weight: 600;
            font-size: 1.3rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .section h3 i {
            margin-right: 8px;
            color: var(--accent);
        }
        
        .step-card {
            background: var(--light);
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--secondary);
            transition: all 0.3s;
        }
        
        .step-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transform: translateX(5px);
        }
        
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--secondary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .step-title {
            font-weight: 600;
            color: var(--primary);
        }
        
        .step-desc {
            color: #495057;
            margin-top: 4px;
            padding-left: 44px;
        }
        
        .step-desc ul {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }
        
        .step-desc ul li {
            margin-bottom: 4px;
        }
        
        .example-box {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin: 0.8rem 0;
            border-left: 4px solid var(--accent);
        }
        
        .example-box .label {
            font-weight: 600;
            color: var(--accent);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-role {
            font-size: 0.8rem;
            padding: 4px 14px;
            border-radius: 20px;
            margin: 2px;
            font-weight: 600;
        }
        .badge-admin { background: #dc3545; color: white; }
        .badge-aprobador { background: #ffc107; color: #212529; }
        .badge-vendedor { background: #17a2b8; color: white; }
        .badge-kam { background: #6f42c1; color: white; }
        
        .badge-estado {
            font-size: 0.8rem;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table-custom thead {
            background: var(--primary);
            color: white;
        }
        
        .table-custom th {
            font-weight: 600;
            padding: 0.8rem 1rem;
        }
        
        .table-custom td {
            padding: 0.8rem 1rem;
            vertical-align: middle;
        }
        
        .table-custom tr:hover {
            background: rgba(45, 109, 168, 0.05);
        }
        
        .alert-tip {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            border-radius: 10px;
            padding: 1rem 1.5rem;
        }
        
        .alert-tip i {
            color: #0c5460;
        }
        
        .alert-warning-custom {
            background: #fff3cd;
            border-color: #ffe69c;
            color: #856404;
            border-radius: 10px;
            padding: 1rem 1.5rem;
        }
        
        .btn-volver {
            background: var(--secondary);
            color: white;
            padding: 0.7rem 2.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-volver:hover {
            background: #1f5a8a;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 109, 168, 0.3);
        }
        
        .btn-imprimir {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
            padding: 0.7rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-imprimir:hover {
            background: var(--secondary);
            color: white;
        }
        
        .footer-manual {
            background: var(--primary);
            color: rgba(255,255,255,0.7);
            padding: 1.5rem 3rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .footer-manual strong {
            color: white;
        }
        
        @media (max-width: 768px) {
            .manual-header { padding: 1.5rem; }
            .manual-body { padding: 1.5rem; }
            .manual-nav .nav-link { padding: 0.3rem 0.8rem; font-size: 0.85rem; }
        }
        
        /* Scroll suave */
        html {
            scroll-behavior: smooth;
        }
        
        /* Badge de nuevo */
        .badge-new {
            background: var(--accent);
            color: white;
            font-size: 0.6rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 6px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="manual-wrapper">
        <!-- ========================================== -->
        <!-- HEADER -->
        <!-- ========================================== -->
        <div class="manual-header">
            <img src="<?php echo BASE_URL; ?>public/img/logo.png" alt="Quillayes Surlat">
            <h1><i class="fas fa-book-open me-3"></i>Manual de Usuario</h1>
            <p class="subtitle">Sistema de Aprobaciones de Descuentos - Quillayes Surlat</p>
            <span class="version"><i class="fas fa-code-branch me-1"></i>Versión 1.0</span>
        </div>
        
        <!-- ========================================== -->
        <!-- NAVEGACIÓN -->
        <!-- ========================================== -->
        <nav class="manual-nav">
            <div class="container-fluid">
                <div class="row g-2 justify-content-center">
                    <div class="col-auto"><a href="#inicio" class="nav-link"><i class="fas fa-home"></i> Inicio</a></div>
                    <div class="col-auto"><a href="#roles" class="nav-link"><i class="fas fa-users"></i> Roles</a></div>
                    <div class="col-auto"><a href="#flujo" class="nav-link"><i class="fas fa-route"></i> Flujo</a></div>
                    <div class="col-auto"><a href="#vendedor" class="nav-link"><i class="fas fa-user-tie"></i> Vendedor</a></div>
                    <div class="col-auto"><a href="#aprobador" class="nav-link"><i class="fas fa-user-check"></i> Aprobador</a></div>
                    <div class="col-auto"><a href="#admin" class="nav-link"><i class="fas fa-user-cog"></i> Admin</a></div>
                    <div class="col-auto"><a href="#estados" class="nav-link"><i class="fas fa-tag"></i> Estados</a></div>
                    <div class="col-auto"><a href="#tramos" class="nav-link"><i class="fas fa-layer-group"></i> Tramos</a></div>
                    <div class="col-auto"><a href="#faq" class="nav-link"><i class="fas fa-question-circle"></i> FAQ</a></div>
                </div>
            </div>
        </nav>
        
        <!-- ========================================== -->
        <!-- CONTENIDO -->
        <!-- ========================================== -->
        <div class="manual-body">
            
            <!-- ========================================== -->
            <!-- SECCIÓN 1: INICIO -->
            <!-- ========================================== -->
            <div id="inicio" class="section">
                <h2><i class="fas fa-home"></i>Bienvenido al Sistema</h2>
                
                <p class="lead">El <strong>Sistema de Aprobaciones de Descuentos</strong> permite gestionar de forma eficiente las solicitudes de descuento para clientes de <strong>Quillayes Surlat</strong>.</p>
                
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-0 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                                <h5>Rápido y Eficiente</h5>
                                <p class="text-muted small">Crea solicitudes en minutos con un flujo guiado paso a paso.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-0 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                                <h5>Seguro y Controlado</h5>
                                <p class="text-muted small">Flujo de aprobación por tramos con notificaciones automáticas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-0 shadow-sm">
                            <div class="card-body">
                                <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                                <h5>Traza y Auditoría</h5>
                                <p class="text-muted small">Registro completo de todas las solicitudes y aprobaciones.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-tip mt-4">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Tip:</strong> Este manual está diseñado para guiarte paso a paso. Si eres nuevo, comienza por la sección de tu rol.
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 2: ROLES -->
            <!-- ========================================== -->
            <div id="roles" class="section">
                <h2><i class="fas fa-users"></i>Roles del Sistema</h2>
                
                <p>Cada usuario tiene un rol específico que define qué acciones puede realizar en el sistema.</p>
                
                <div class="table-responsive table-custom mt-3">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Rol</th>
                                <th>Descripción</th>
                                <th>Permisos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge-role badge-admin"><i class="fas fa-crown me-1"></i> Admin</span></td>
                                <td>Administrador del sistema</td>
                                <td>✅ Todos los permisos<br>✅ Configuración del sistema<br>✅ Gestión de usuarios</td>
                            </tr>
                            <tr>
                                <td><span class="badge-role badge-aprobador"><i class="fas fa-check-circle me-1"></i> Aprobador</span></td>
                                <td>Aprueba o rechaza solicitudes</td>
                                <td>✅ Revisar solicitudes<br>✅ Aprobar/Rechazar<br>✅ Dejar comentarios</td>
                            </tr>
                            <tr>
                                <td><span class="badge-role badge-vendedor"><i class="fas fa-store me-1"></i> Vendedor</span></td>
                                <td>Crea solicitudes de descuento</td>
                                <td>✅ Crear solicitudes<br>✅ Editar borradores<br>✅ Ver estado de sus solicitudes</td>
                            </tr>
                            <tr>
                                <td><span class="badge-role badge-kam"><i class="fas fa-handshake me-1"></i> KAM</span></td>
                                <td>Key Account Manager</td>
                                <td>✅ Crear solicitudes<br>✅ Acceso a clientes de su subcanal<br>✅ Seguimiento de cuentas clave</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 3: FLUJO DE TRABAJO -->
            <!-- ========================================== -->
            <div id="flujo" class="section">
                <h2><i class="fas fa-route"></i>Flujo de Trabajo</h2>
                
                <p>El proceso completo desde que se crea una solicitud hasta que es aprobada o rechazada.</p>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">1</span>
                        <span class="step-title">🧑‍💼 Vendedor crea solicitud</span>
                    </div>
                    <div class="step-desc">
                        El vendedor ingresa al sistema, busca el cliente por código, agrega los SKUs con precios propuestos y define las fechas de la promoción.
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: #ffc107;">
                    <div>
                        <span class="step-number">2</span>
                        <span class="step-title">💾 Guardar Borrador <span class="badge-new">Opcional</span></span>
                    </div>
                    <div class="step-desc">
                        Si no está listo para enviar, puede guardar como <strong>Borrador</strong> y continuar después. La solicitud no se notifica a nadie.
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: #17a2b8;">
                    <div>
                        <span class="step-number">3</span>
                        <span class="step-title">📤 Enviar Solicitud</span>
                    </div>
                    <div class="step-desc">
                        El vendedor revisa el resumen y confirma el envío. El sistema calcula el descuento promedio y determina los tramos de aprobación.
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: #fd7e14;">
                    <div>
                        <span class="step-number">4</span>
                        <span class="step-title">📧 Notificación a Aprobadores</span>
                    </div>
                    <div class="step-desc">
                        Cada aprobador asignado al tramo correspondiente recibe un correo electrónico con el enlace para revisar la solicitud.
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: #6f42c1;">
                    <div>
                        <span class="step-number">5</span>
                        <span class="step-title">👀 Aprobador Revisa</span>
                    </div>
                    <div class="step-desc">
                        El aprobador ingresa al sistema, ve el detalle de la solicitud, puede modificar la fecha de término si lo considera necesario y debe dejar un comentario.
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: var(--success);">
                    <div>
                        <span class="step-number">6</span>
                        <span class="step-title">✅ Aprobación o Rechazo</span>
                    </div>
                    <div class="step-desc">
                        <strong>Todos los aprobadores deben aprobar</strong> para que la solicitud sea <span class="badge bg-success">Aprobada</span>.<br>
                        Si <strong>uno solo rechaza</strong>, la solicitud queda <span class="badge bg-danger">Rechazada</span>.
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: var(--secondary);">
                    <div>
                        <span class="step-number">7</span>
                        <span class="step-title">📩 Notificación al Vendedor</span>
                    </div>
                    <div class="step-desc">
                        El vendedor recibe un correo con el resultado de su solicitud. Si fue rechazada, incluye los comentarios del aprobador.
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-info-circle me-1"></i> Ejemplo Real</div>
                    <p class="mb-0">
                        <strong>Vendedor:</strong> Juan Pérez crea una solicitud para <strong>Supermercados Líder</strong> con 5 SKUs y un descuento promedio del 15%.<br>
                        <strong>Tramos asignados:</strong> Tramo 1 (Aprobación automática) + Tramo 2 (Aprobador: María González).<br>
                        <strong>Resultado:</strong> María aprueba → Solicitud <span class="badge bg-success">Aprobada</span> ✅
                    </p>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 4: GUÍA PARA VENDEDORES -->
            <!-- ========================================== -->
            <div id="vendedor" class="section">
                <h2><i class="fas fa-user-tie"></i>Guía para Vendedores</h2>
                
                <h3><i class="fas fa-plus-circle"></i>Paso 1: Crear una Nueva Solicitud</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">1</span>
                        <span class="step-title">Ir a "Nueva Solicitud"</span>
                    </div>
                    <div class="step-desc">
                        En el menú lateral, haz clic en <strong>"Nueva Solicitud"</strong>.
                    </div>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">2</span>
                        <span class="step-title">Buscar Cliente</span>
                    </div>
                    <div class="step-desc">
                        Ingresa el <strong>código del cliente</strong> (ej: <code>CL001</code>) y haz clic en el botón de búsqueda.
                        <ul>
                            <li>Si el cliente existe, verás sus datos (nombre, subcanal, canal).</li>
                            <li>Si no existe, verifica el código o contacta a un administrador.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-search me-1"></i> Ejemplo</div>
                    <p class="mb-0">
                        Ingresas <code>CL001</code> → El sistema muestra: <strong>Supermercados Líder</strong> | Subcanal: Retail | Canal: Retail
                    </p>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">3</span>
                        <span class="step-title">Agregar SKUs</span>
                    </div>
                    <div class="step-desc">
                        <ul>
                            <li>Ingresa el <strong>código SKU</strong> (ej: <code>LEC001</code>).</li>
                            <li>El sistema mostrará el <strong>Precio Lista</strong> automáticamente.</li>
                            <li>Ingresa el <strong>Precio Propuesto</strong> o el <strong>% de Descuento</strong>.</li>
                            <li>Ingresa el <strong>Volumen Esperado</strong> (cantidad a vender).</li>
                            <li>Haz clic en <strong>"Agregar SKU"</strong>.</li>
                            <li>Puedes agregar hasta <strong>30 SKUs</strong> por solicitud.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-box me-1"></i> Ejemplo de SKU</div>
                    <p class="mb-0">
                        <strong>SKU:</strong> LEC001<br>
                        <strong>Material:</strong> Leche Entera 1L<br>
                        <strong>Precio Lista:</strong> $1.200<br>
                        <strong>Precio Propuesto:</strong> $1.000<br>
                        <strong>% Descuento:</strong> 16.7%<br>
                        <strong>Volumen Esperado:</strong> 500 unidades
                    </p>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">4</span>
                        <span class="step-title">Definir Fechas</span>
                    </div>
                    <div class="step-desc">
                        <ul>
                            <li><strong>Fecha de Inicio:</strong> La promoción debe comenzar a partir de mañana.</li>
                            <li><strong>Fecha de Término:</strong> No puede superar 365 días desde el inicio.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">5</span>
                        <span class="step-title">Guardar Borrador o Enviar</span>
                    </div>
                    <div class="step-desc">
                        <ul>
                            <li><strong>Guardar Borrador:</strong> La solicitud se guarda pero <strong>NO</strong> se notifica a nadie. Puedes editar después.</li>
                            <li><strong>Enviar Solicitud:</strong> La solicitud entra al flujo de aprobación y se notifica a los aprobadores.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-warning-custom mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Importante:</strong> Una vez enviada, <strong>NO</strong> puedes editar la solicitud. Solo los aprobadores pueden modificar la fecha de término.
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 5: GUÍA PARA APROBADORES -->
            <!-- ========================================== -->
            <div id="aprobador" class="section">
                <h2><i class="fas fa-user-check"></i>Guía para Aprobadores</h2>
                
                <h3><i class="fas fa-tasks"></i>Paso 1: Ver Solicitudes Pendientes</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">1</span>
                        <span class="step-title">Ir a "Mis Aprobaciones"</span>
                    </div>
                    <div class="step-desc">
                        En el menú lateral, haz clic en <strong>"Mis Aprobaciones"</strong>.
                    </div>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">2</span>
                        <span class="step-title">Revisar Lista</span>
                    </div>
                    <div class="step-desc">
                        Verás todas las solicitudes que necesitan tu aprobación. Las pendientes tienen el estado <span class="badge bg-warning">Pendiente</span>.
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-list me-1"></i> Ejemplo</div>
                    <p class="mb-0">
                        La solicitud <strong>SOL-20260101-ABC1</strong> está en estado <span class="badge bg-warning">Pendiente</span> → Debes revisarla.
                    </p>
                </div>
                
                <h3><i class="fas fa-search"></i>Paso 2: Evaluar la Solicitud</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">3</span>
                        <span class="step-title">Revisar Detalle</span>
                    </div>
                    <div class="step-desc">
                        Haz clic en <strong>"Revisar"</strong> para ver el detalle completo de la solicitud:
                        <ul>
                            <li>Cliente y vendedor</li>
                            <li>Lista de SKUs con precios y descuentos</li>
                            <li>Descuento promedio</li>
                            <li>Fechas de inicio y término</li>
                            <li>Motivo (si fue ingresado)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">4</span>
                        <span class="step-title">Modificar Fecha (Opcional)</span>
                    </div>
                    <div class="step-desc">
                        Si lo consideras necesario, puedes <strong>modificar la fecha de término</strong> antes de aprobar.
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-calendar-alt me-1"></i> Ejemplo</div>
                    <p class="mb-0">
                        La solicitud tiene fecha de término <strong>15/01/2026</strong>. Consideras que debe extenderse una semana más, la cambias a <strong>22/01/2026</strong>.
                    </p>
                </div>
                
                <h3><i class="fas fa-check-circle"></i>Paso 3: Decidir</h3>
                
                <div class="step-card" style="border-left-color: var(--success);">
                    <div>
                        <span class="step-number">5</span>
                        <span class="step-title">Aprobar</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ingresa un <strong>comentario obligatorio</strong> (ej: "Aprobado según política comercial").</li>
                            <li>Haz clic en <strong>"Aprobar"</strong>.</li>
                            <li>La solicitud avanzará al siguiente tramo o se aprobará definitivamente.</li>
                        </ol>
                    </div>
                </div>
                
                <div class="step-card" style="border-left-color: var(--danger);">
                    <div>
                        <span class="step-number">6</span>
                        <span class="step-title">Rechazar</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ingresa un <strong>comentario obligatorio</strong> explicando el motivo (ej: "El descuento excede el margen permitido").</li>
                            <li>Haz clic en <strong>"Rechazar"</strong>.</li>
                            <li>La solicitud queda <span class="badge bg-danger">Rechazada</span> y se notifica al vendedor.</li>
                        </ol>
                    </div>
                </div>
                
                <div class="alert alert-warning-custom mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Importante:</strong> El comentario es <strong>OBLIGATORIO</strong> tanto para aprobar como para rechazar. El vendedor lo verá en la notificación.
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 6: GUÍA PARA ADMINISTRADORES -->
            <!-- ========================================== -->
            <div id="admin" class="section">
                <h2><i class="fas fa-user-cog"></i>Guía para Administradores</h2>
                
                <h3><i class="fas fa-users"></i>Gestión de Usuarios</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">1</span>
                        <span class="step-title">Crear Usuario</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ir a <strong>Configuración → Usuarios</strong></li>
                            <li>Haz clic en <strong>"Agregar Usuario"</strong></li>
                            <li>Completa los datos: nombre, apellido, correo (@quillayessurlat.cl), rol, cargo.</li>
                            <li>Asigna una contraseña temporal.</li>
                            <li>Haz clic en <strong>"Registrar Usuario"</strong></li>
                        </ol>
                    </div>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">2</span>
                        <span class="step-title">Activar/Desactivar Usuario</span>
                    </div>
                    <div class="step-desc">
                        En la lista de usuarios, usa el botón <span class="badge bg-warning"><i class="fas fa-ban"></i></span> o <span class="badge bg-success"><i class="fas fa-check"></i></span> para cambiar el estado del usuario.
                    </div>
                </div>
                
                <h3><i class="fas fa-boxes"></i>Gestión de Materiales y Precios</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">3</span>
                        <span class="step-title">Agregar Material</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ir a <strong>Configuración → Materiales</strong></li>
                            <li>Haz clic en <strong>"Agregar Material"</strong></li>
                            <li>Ingresa SKU, nombre, familia y unidad de carga.</li>
                            <li>Haz clic en <strong>"Guardar"</strong></li>
                        </ol>
                    </div>
                </div>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">4</span>
                        <span class="step-title">Asignar Precio</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ir a <strong>Configuración → Precios</strong></li>
                            <li>Haz clic en <strong>"Agregar Precio"</strong></li>
                            <li>Selecciona SKU, subcanal, ingresa el precio lista y las fechas de vigencia.</li>
                            <li>Haz clic en <strong>"Guardar"</strong></li>
                        </ol>
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-tag me-1"></i> Ejemplo</div>
                    <p class="mb-0">
                        <strong>SKU:</strong> LEC001<br>
                        <strong>Subcanal:</strong> Retail<br>
                        <strong>Precio Lista:</strong> $1.200<br>
                        <strong>Vigencia:</strong> 01/01/2026 - 31/12/2026
                    </p>
                </div>
                
                <h3><i class="fas fa-cog"></i>Configuración de Tramos</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">5</span>
                        <span class="step-title">Configurar Tramos</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ir a <strong>Configuración → Tramos</strong></li>
                            <li>Haz clic en <strong>"Agregar Tramo"</strong></li>
                            <li>Define canal, tramo, rango de descuento (mínimo y máximo).</li>
                            <li>Activa <strong>"Aprobación Automática"</strong> si aplica.</li>
                            <li>Haz clic en <strong>"Guardar Tramo"</strong></li>
                        </ol>
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-layer-group me-1"></i> Ejemplo de Configuración</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <tr><th>Canal</th><th>Tramo</th><th>Descuento Min</th><th>Descuento Max</th><th>Auto</th></tr>
                            <tr><td>Cobertura Nacional</td><td>1</td><td>0%</td><td>10%</td><td>✅ Sí</td></tr>
                            <tr><td>Cobertura Nacional</td><td>2</td><td>10%</td><td>20%</td><td>❌ No</td></tr>
                            <tr><td>Cobertura Nacional</td><td>3</td><td>20%</td><td>50%</td><td>❌ No</td></tr>
                        </table>
                    </div>
                </div>
                
                <h3><i class="fas fa-user-check"></i>Configuración de Aprobadores</h3>
                
                <div class="step-card">
                    <div>
                        <span class="step-number">6</span>
                        <span class="step-title">Asignar Aprobadores</span>
                    </div>
                    <div class="step-desc">
                        <ol>
                            <li>Ir a <strong>Configuración → Aprobadores</strong></li>
                            <li>Haz clic en <strong>"Agregar Aprobador"</strong></li>
                            <li>Selecciona canal, tramo, email del aprobador y orden de prioridad.</li>
                            <li>Haz clic en <strong>"Guardar Aprobador"</strong></li>
                        </ol>
                    </div>
                </div>
                
                <div class="example-box">
                    <div class="label"><i class="fas fa-user-check me-1"></i> Ejemplo</div>
                    <p class="mb-0">
                        <strong>Canal:</strong> Cobertura Nacional<br>
                        <strong>Tramo:</strong> 2<br>
                        <strong>Aprobador:</strong> maria.gonzalez@quillayessurlat.cl<br>
                        <strong>Orden:</strong> 1 (prioridad más alta)
                    </p>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 7: ESTADOS -->
            <!-- ========================================== -->
            <div id="estados" class="section">
                <h2><i class="fas fa-tag"></i>Estados de las Solicitudes</h2>
                
                <p>Cada solicitud pasa por diferentes estados a lo largo del proceso. Aquí te explicamos cada uno.</p>
                
                <div class="table-responsive table-custom mt-3">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Color</th>
                                <th>Descripción</th>
                                <th>Acciones posibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge-estado bg-secondary">Borrador</span></td>
                                <td>⚪ Secundario</td>
                                <td>Guardada pero no enviada</td>
                                <td>✅ Editar<br>✅ Enviar</td>
                            </tr>
                            <tr>
                                <td><span class="badge-estado bg-info">Enviada</span></td>
                                <td>🔵 Info</td>
                                <td>Enviada, esperando asignación</td>
                                <td>👁️ Solo ver</td>
                            </tr>
                            <tr>
                                <td><span class="badge-estado bg-warning">En Aprobación</span></td>
                                <td>🟠 Warning</td>
                                <td>En proceso de aprobación</td>
                                <td>👁️ Solo ver</td>
                            </tr>
                            <tr>
                                <td><span class="badge-estado bg-success">Aprobada</span></td>
                                <td>🟢 Success</td>
                                <td>Aprobada por todos los aprobadores</td>
                                <td>👁️ Ver detalle</td>
                            </tr>
                            <tr>
                                <td><span class="badge-estado bg-danger">Rechazada</span></td>
                                <td>🔴 Danger</td>
                                <td>Rechazada por un aprobador</td>
                                <td>👁️ Ver comentarios</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="example-box mt-3">
                    <div class="label"><i class="fas fa-road me-1"></i> Ejemplo de Recorrido</div>
                    <p class="mb-0">
                        <span class="badge bg-secondary">Borrador</span> → 
                        <span class="badge bg-info">Enviada</span> → 
                        <span class="badge bg-warning">En Aprobación</span> → 
                        <span class="badge bg-success">Aprobada</span> ✅
                    </p>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 8: TRAMOS -->
            <!-- ========================================== -->
            <div id="tramos" class="section">
                <h2><i class="fas fa-layer-group"></i>Configuración de Tramos</h2>
                
                <p>Los tramos determinan quién debe aprobar una solicitud según el descuento promedio y el canal del cliente.</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-dark text-white">
                                <strong><i class="fas fa-globe me-2"></i>Cobertura Nacional</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered mb-0">
                                    <tr>
                                        <th>Tramo</th>
                                        <th>Descuento</th>
                                        <th>Aprobación</th>
                                    </tr>
                                    <tr>
                                        <td><strong>Tramo 1</strong></td>
                                        <td>0% - 10%</td>
                                        <td><span class="badge bg-success">Automática</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tramo 2</strong></td>
                                        <td>10% - 20%</td>
                                        <td><span class="badge bg-warning">Manual</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tramo 3</strong></td>
                                        <td>20% - 50%</td>
                                        <td><span class="badge bg-warning">Manual</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-dark text-white">
                                <strong><i class="fas fa-store me-2"></i>Retail</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered mb-0">
                                    <tr>
                                        <th>Tramo</th>
                                        <th>Descuento</th>
                                        <th>Aprobación</th>
                                    </tr>
                                    <tr>
                                        <td><strong>Tramo 1</strong></td>
                                        <td>0% - 20%</td>
                                        <td><span class="badge bg-warning">Manual</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tramo 2</strong></td>
                                        <td>20% - 35%</td>
                                        <td><span class="badge bg-warning">Manual</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tramo 3</strong></td>
                                        <td>35% - 50%</td>
                                        <td><span class="badge bg-warning">Manual</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="example-box mt-3">
                    <div class="label"><i class="fas fa-calculator me-1"></i> Ejemplo de Cálculo</div>
                    <p class="mb-0">
                        <strong>Cliente:</strong> Supermercados Líder → Canal: Retail<br>
                        <strong>Descuento promedio:</strong> 25%<br>
                        <strong>Tramos asignados:</strong> Tramo 1 (0-20%) + Tramo 2 (20-35%)<br>
                        <strong>Aprobadores:</strong> 2 aprobadores deben revisar la solicitud.
                    </p>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- SECCIÓN 9: PREGUNTAS FRECUENTES -->
            <!-- ========================================== -->
            <div id="faq" class="section">
                <h2><i class="fas fa-question-circle"></i>Preguntas Frecuentes (FAQ)</h2>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <strong>¿Puedo editar una solicitud después de enviarla?</strong>
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>No.</strong> Una vez enviada, la solicitud entra al flujo de aprobación y <strong>no se puede editar</strong>. Solo los aprobadores pueden modificar la fecha de término al momento de aprobar.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <strong>¿Qué pasa si un aprobador rechaza la solicitud?</strong>
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Si un aprobador <strong>rechaza</strong> la solicitud, esta queda <span class="badge bg-danger">Rechazada</span> automáticamente. El vendedor recibe una notificación con los comentarios del aprobador.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <strong>¿Cuántos SKUs puedo agregar en una solicitud?</strong>
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                El límite máximo es de <strong>30 SKUs</strong> por solicitud. Si necesitas agregar más, contacta a un administrador.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <strong>¿Cómo sé quién debe aprobar mi solicitud?</strong>
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                El sistema <strong>determina automáticamente</strong> los tramos según el descuento promedio y el canal del cliente. Los aprobadores asignados a esos tramos recibirán la notificación.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <strong>¿Puedo ver el historial de aprobaciones?</strong>
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>Sí.</strong> En el detalle de cada solicitud, puedes ver todas las aprobaciones realizadas, los comentarios y las fechas de resolución.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                <strong>¿Qué hago si el precio de lista no aparece?</strong>
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Si el sistema muestra que el precio no existe, debes <strong>notificar al Jefe de Subcanal</strong> para que gestione la creación del precio en el sistema. El sistema enviará automáticamente una notificación al Jefe de Subcanal.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                <strong>¿Qué significa "Aprobación Automática"?</strong>
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Cuando un tramo tiene <strong>"Aprobación Automática"</strong> activada, la solicitud <strong>no necesita ser revisada manualmente</strong> por un aprobador para ese tramo. Se aprueba automáticamente.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                <strong>¿Los aprobadores reciben notificaciones por correo?</strong>
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>Sí.</strong> Los aprobadores reciben un correo electrónico cuando:
                                <ul class="mb-0">
                                    <li>Se crea una nueva solicitud que requiere su aprobación.</li>
                                    <li>La solicitud fue aprobada o rechazada (notificación al vendedor).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- BOTONES DE ACCIÓN -->
            <!-- ========================================== -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-3 border-top">
                <div>
                    <span class="text-muted small">
                        <i class="fas fa-calendar-alt me-1"></i> Última actualización: <?php echo date('d/m/Y'); ?>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn-imprimir">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/login" class="btn-volver">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Login
                    </a>
                </div>
            </div>
            
        </div>
        
        <!-- ========================================== -->
        <!-- FOOTER -->
        <!-- ========================================== -->
        <div class="footer-manual">
            <p class="mb-0">
                <i class="fas fa-check-circle me-1"></i>
                <strong>Quillayes Surlat</strong> - Sistema de Aprobaciones de Descuentos
                <span class="mx-2">|</span>
                © <?php echo date('Y'); ?> Todos los derechos reservados
                <span class="mx-2">|</span>
                Versión 1.0
            </p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll suave al hacer clic en los enlaces de navegación
        document.querySelectorAll('.manual-nav .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                // Actualizar URL sin recargar
                history.pushState(null, null, '#' + targetId);
            });
        });
        
        // Si la URL tiene un hash, hacer scroll al elemento
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const targetId = window.location.hash.substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            }
        });
        
        // Cerrar el acordeón al hacer scroll (solo para mejor UX)
        console.log('Manual de Usuario cargado correctamente ✅');
    </script>
</body>
</html>