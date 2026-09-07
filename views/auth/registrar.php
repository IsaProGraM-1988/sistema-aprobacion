<?php
$titulo = 'Registrar Usuario';
?>
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-plus me-2"></i>Registrar Nuevo Usuario</h1>
                <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Datos del Usuario</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="apellido" 
                                       value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       placeholder="usuario@quillayessurlat.cl" required>
                                <small class="text-muted">Debe ser un correo corporativo @quillayessurlat.cl</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" name="rol" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol; ?>" 
                                            <?php echo (($_POST['rol'] ?? '') === $rol) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($rol); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cargo</label>
                                <input type="text" class="form-control" name="cargo" 
                                       value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>" 
                                       placeholder="Ej: Vendedor, KAM, Jefe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Subcanal</label>
                                <select class="form-select" name="subcanal">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($subcanales as $subcanal): ?>
                                        <option value="<?php echo $subcanal; ?>"
                                            <?php echo (($_POST['subcanal'] ?? '') === $subcanal) ? 'selected' : ''; ?>>
                                            <?php echo $subcanal; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" 
                                       minlength="6" required>
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmar" 
                                       minlength="6" required>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Registrar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de correo corporativo
document.querySelector('input[name="email"]').addEventListener('blur', function() {
    const email = this.value;
    if (email && !email.includes('@quillayessurlat.cl')) {
        this.classList.add('is-invalid');
        if (!this.nextElementSibling?.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Debe usar un correo corporativo @quillayessurlat.cl';
            this.parentNode.appendChild(feedback);
        }
    } else {
        this.classList.remove('is-invalid');
        const feedback = this.parentNode.querySelector('.invalid-feedback');
        if (feedback) feedback.remove();
    }
});

// Validación de contraseñas coincidentes
document.querySelector('form').addEventListener('submit', function(e) {
    const pass1 = document.querySelector('input[name="password"]').value;
    const pass2 = document.querySelector('input[name="password_confirmar"]').value;
    if (pass1 !== pass2) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
    }
});
</script>