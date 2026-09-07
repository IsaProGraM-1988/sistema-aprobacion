<?php
// Layout principal del sistema
if (!Session::isLoggedIn()) {
    header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
    exit;
}

// Obtener ruta actual para resaltar menú
$currentRoute = $_GET['route'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Sistema de Aprobaciones Quillayes Surlat'; ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>public/img/logo.png">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar bg-dark">
            <div class="sidebar-header text-center py-4">
                <img src="<?php echo BASE_URL; ?>public/img/logo.png" alt="Quillayes Surlat" class="img-fluid mb-2" style="max-height: 80px;">
                <h6 class="text-white mt-2 mb-0">Sistema de Aprobaciones</h6>
                <hr class="bg-light mt-3">
            </div>
            <ul class="nav flex-column sidebar-nav">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=dashboard" class="nav-link <?php echo ($currentRoute === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-th-large me-2"></i> Dashboard
                    </a>
                </li>
                <?php if (in_array(Session::get('rol'), ['vendedor', 'kam', 'admin'])): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/nueva" class="nav-link <?php echo ($currentRoute === 'solicitudes/nueva') ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle me-2"></i> Nueva Solicitud
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/mis-solicitudes" class="nav-link <?php echo ($currentRoute === 'solicitudes/mis-solicitudes') ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt me-2"></i> Mis Solicitudes
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array(Session::get('rol'), ['aprobador', 'admin'])): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=aprobaciones/mis-aprobaciones" class="nav-link <?php echo ($currentRoute === 'aprobaciones/mis-aprobaciones') ? 'active' : ''; ?>">
                        <i class="fas fa-tasks me-2"></i> Mis Aprobaciones
                    </a>
                </li>
                <?php endif; ?>
                <?php if (Session::get('rol') === 'admin'): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/todas-solicitudes" class="nav-link <?php echo ($currentRoute === 'admin/todas-solicitudes') ? 'active' : ''; ?>">
                        <i class="fas fa-list me-2"></i> Todas las Solicitudes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="nav-link <?php echo ($currentRoute === 'admin/configuracion') ? 'active' : ''; ?>">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item mt-4 border-top border-secondary pt-3">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/perfil" class="nav-link <?php echo ($currentRoute === 'auth/perfil') ? 'active' : ''; ?>">
                        <i class="fas fa-user me-2"></i> Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div id="content" class="content">
        
            <!-- Mensajes del sistema -->
            <?php
            $mensaje = Session::getMensaje();
            if (!empty($mensaje['mensaje'])):
            ?>
            <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $mensaje['tipo'] === 'success' ? 'check-circle' : ($mensaje['tipo'] === 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
                <?php echo htmlspecialchars($mensaje['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="container-fluid">
                <?php echo $content ?? ''; ?>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SCRIPTS -->
    <!-- ============================================================ -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const URL_SISTEMA = '<?php echo BASE_URL; ?>public/index.php?route=';
        console.log("✅ BASE_URL:", BASE_URL);
        console.log("✅ URL_SISTEMA:", URL_SISTEMA);
        console.log("✅ jQuery cargado:", typeof $ !== 'undefined');
    </script>
    
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/solicitudes.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/validaciones.js"></script>
    
    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>