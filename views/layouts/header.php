<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Sistema de Aprobaciones Quillayes Surlat'; ?></title>
    
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
    <?php if (Session::isLoggedIn()): ?>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar bg-dark">
            <div class="sidebar-header">
                <h3 class="text-white text-center py-3">
                    <i class="fas fa-check-circle me-2"></i>Quillayes
                </h3>
                <hr class="bg-light">
            </div>
            
            <ul class="nav flex-column sidebar-nav">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>dashboard/" class="nav-link">
                        <i class="fas fa-th-large me-2"></i> Dashboard
                    </a>
                </li>
                
                <?php if (in_array(Session::get('rol'), ['vendedor', 'kam'])): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>solicitudes/nueva" class="nav-link">
                        <i class="fas fa-plus-circle me-2"></i> Nueva Solicitud
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>solicitudes/mis-solicitudes" class="nav-link">
                        <i class="fas fa-file-alt me-2"></i> Mis Solicitudes
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array(Session::get('rol'), ['aprobador', 'admin'])): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>aprobaciones/mis-aprobaciones" class="nav-link">
                        <i class="fas fa-tasks me-2"></i> Mis Aprobaciones
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Session::get('rol') === 'admin'): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>admin/todas-solicitudes" class="nav-link">
                        <i class="fas fa-list me-2"></i> Todas las Solicitudes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>admin/configuracion" class="nav-link">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item mt-4">
                    <a href="<?php echo BASE_URL; ?>auth/logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div id="content" class="content">
            <!-- Barra superior -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
                <div class="container-fluid">
                    <button type="button" id="sidebarToggle" class="btn btn-outline-secondary me-3">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <span class="navbar-text">
                        <span class="fw-bold"><?php echo Session::get('nombre') . ' ' . Session::get('apellido'); ?></span>
                        <small class="text-muted ms-2">(<?php echo ucfirst(Session::get('rol')); ?>)</small>
                    </span>
                    
                    <div class="dropdown ms-auto">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text"><?php echo Session::get('email'); ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                </div>
            </nav>
    <?php endif; ?>
    
    <div class="container-fluid">