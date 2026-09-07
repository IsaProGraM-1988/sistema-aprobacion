<!-- Modal Agregar Tramo -->
<div class="modal fade" id="modalTramo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Agregar Tramo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="guardar_tramo">
                    
                    <div class="mb-3">
                        <label class="form-label">Canal <span class="text-danger">*</span></label>
                        <select class="form-select" name="canal" required>
                            <option value="Cobertura Nacional">Cobertura Nacional</option>
                            <option value="Retail">Retail</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Familia</label>
                        <input type="text" class="form-control" name="familia" placeholder="Dejar vacío para todas">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tramo <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="tramo" required min="1">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Descuento Mínimo % <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="descuento_min" required step="0.1" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Descuento Máximo % <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="descuento_max" required step="0.1" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="aprobacion_automatica" value="1">
                            <label class="form-check-label">Aprobación Automática</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Tramo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Agregar Aprobador -->
<div class="modal fade" id="modalAprobador" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Configurar Aprobador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="guardar_aprobador">
                    
                    <div class="mb-3">
                        <label class="form-label">Canal <span class="text-danger">*</span></label>
                        <select class="form-select" name="canal" required>
                            <option value="Cobertura Nacional">Cobertura Nacional</option>
                            <option value="Retail">Retail</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tramo <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="tramo" required min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email del Aprobador <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email_aprobador" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Orden <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="orden" required min="0" value="0">
                        <small class="text-muted">Menor número = Mayor prioridad</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Aprobador</button>
                </div>
            </form>
        </div>
    </div>
</div>