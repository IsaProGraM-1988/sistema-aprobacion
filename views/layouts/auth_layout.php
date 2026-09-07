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
    
    <link href="<?php echo BASE_URL; ?>public/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2a3a 0%, #2d6da8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .auth-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-logo h2 {
            color: #1a2a3a;
            font-weight: 700;
        }
        .auth-logo p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .btn-primary {
            background-color: #2d6da8;
            border-color: #2d6da8;
            padding: 0.6rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1f5a8a;
            border-color: #1f5a8a;
        }
        .auth-link {
            color: #2d6da8;
            text-decoration: none;
        }
        .auth-link:hover {
            text-decoration: underline;
        }
        .form-control:focus {
            border-color: #2d6da8;
            box-shadow: 0 0 0 0.2rem rgba(45, 109, 168, 0.25);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-check-circle fa-3x text-primary mb-3"></i>
                <h2>Quillayes Surlat</h2>
                <p>Sistema de Aprobaciones</p>
            </div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $content; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>