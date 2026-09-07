<?php
$titulo = 'Cambiar Contraseña';
?>
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-key me-2"></i>Cambiar Contraseña</h1>
                <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/perfil" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
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
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Actualizar Contraseña</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña Actual <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_actual" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_nueva" 
                                   minlength="6" required>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirmar" 
                                   minlength="6" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/perfil" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-shield-alt me-2"></i>Recomendaciones de seguridad:</h6>
                    <ul class="mb-0 small text-muted">
                        <li>Use al menos 6 caracteres</li>
                        <li>Combine letras mayúsculas, minúsculas y números</li>
                        <li>No use contraseñas que haya usado anteriormente</li>
                        <li>No comparta su contraseña con nadie</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const pass1 = document.querySelector('input[name="password_nueva"]').value;
    const pass2 = document.querySelector('input[name="password_confirmar"]').value;
    if (pass1 !== pass2) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
    }
});
</script>