<?php
// Asegurar que las constantes estén cargadas si se accede directo
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
    <title>Login - Sistema de Aprobaciones Quillayes Surlat</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>public/img/logo.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a2a3a 0%, #2d6da8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 2.5rem;
            max-width: 450px;
            width: 100%;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-container img {
            max-width: 220px;
            height: auto;
        }
        .logo-container h4 {
            color: #1a2a3a;
            font-weight: 700;
            margin-top: 0.5rem;
        }
        .logo-container p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .btn-login {
            background: #2d6da8;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #1f5a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 109, 168, 0.3);
        }
        
        .btn-manual {
            background: transparent;
            border: 2px solid #2d6da8;
            color: #2d6da8;
            padding: 0.6rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
            width: 100%;
            border-radius: 8px;
        }
        .btn-manual:hover {
            background: #2d6da8;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 109, 168, 0.2);
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.2rem 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .form-control:focus {
            border-color: #2d6da8;
            box-shadow: 0 0 0 0.2rem rgba(45, 109, 168, 0.25);
        }
        .input-group-text {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .footer-login {
            text-align: center;
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .footer-login small {
            color: #6c757d;
        }
        
        .footer-login .admin-credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .footer-login .admin-credentials code {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem;
            }
            .logo-container img {
                max-width: 150px;
            }
            .btn-login, .btn-manual {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- ========================================== -->
        <!-- LOGO -->
        <!-- ========================================== -->
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>public/img/logo.png" alt="Quillayes Surlat">
            <h4>Sistema de Aprobaciones</h4>
            <p>Quillayes Surlat</p>
        </div>
        
        <!-- ========================================== -->
        <!-- ERRORES -->
        <!-- ========================================== -->
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- ========================================== -->
        <!-- FORMULARIO DE LOGIN -->
        <!-- ========================================== -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="nombre@quillayessurlat.cl" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <small class="text-muted">Solo correos corporativos @quillayessurlat.cl</small>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            
            <!-- ========================================== -->
            <!-- BOTÓN INICIAR SESIÓN -->
            <!-- ========================================== -->
            <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
            </button>
        </form>
        
        <!-- ========================================== -->
        <!-- DIVISOR -->
        <!-- ========================================== -->
        <div class="divider">
            <span>o</span>
        </div>
        
        <!-- ========================================== -->
        <!-- BOTÓN MANUAL - ABRE EN NUEVA PESTAÑA -->
        <!-- ========================================== -->
        <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/manual" 
           target="_blank" 
           class="btn btn-manual">
            <i class="fas fa-book me-2"></i>📖 Manual de Usuario
        </a>
        
        <!-- ========================================== -->
        <!-- FOOTER -->
        <!-- ========================================== -->
        <div class="footer-login">
            <small>
                <p class="mb-1">Sistema de Aprobaciones © <?php echo date('Y'); ?></p>
            </small>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- SCRIPTS -->
    <!-- ========================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>