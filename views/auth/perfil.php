<?php
$titulo = 'Mi Perfil';
?>
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user me-2"></i>Mi Perfil</h1>
                <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/cambiar-password" class="btn btn-outline-primary">
                    <i class="fas fa-key me-2"></i>Cambiar Contraseña
                </a>
            </div>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Información Personal</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="apellido" 
                                       value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Correo Electrónico</label>
                                <input type="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" disabled>
                                <small class="text-muted">El correo no se puede modificar</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rol</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo ucfirst($usuario['rol'] ?? ''); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cargo</label>
                                <input type="text" class="form-control" name="cargo" 
                                       value="<?php echo htmlspecialchars($usuario['cargo'] ?? ''); ?>">
                            </div>
                            <?php if (!empty($usuario['subcanal'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Subcanal</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($usuario['subcanal']); ?>" disabled>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Cuenta</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Último acceso:</strong></p>
                            <p class="text-muted">
                                <?php 
                                echo isset($usuario['ultimo_acceso']) && $usuario['ultimo_acceso'] 
                                    ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) 
                                    : 'Nunca'; 
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Miembro desde:</strong></p>
                            <p class="text-muted">
                                <?php 
                                echo isset($usuario['fecha_creacion']) 
                                    ? date('d/m/Y', strtotime($usuario['fecha_creacion'])) 
                                    : 'N/A'; 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>