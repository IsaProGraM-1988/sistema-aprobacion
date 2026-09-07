<?php
$titulo = 'Detalle de Aprobación';
$csrfToken = Session::generarTokenCSRF();
$scripts = '
<script>
const CSRF_TOKEN = "' . $csrfToken . '";
const ID_SOLICITUD = ' . ($solicitud['id'] ?? 0) . ';

$(document).ready(function() {
    console.log("🔍 Debug - Iniciando detalle de aprobación");
    console.log("🔍 ID Solicitud:", ID_SOLICITUD);
    console.log("🔍 CSRF Token:", CSRF_TOKEN);
    console.log("🔍 Botón Aprobar existe:", $("#btnAprobar").length > 0);
    console.log("🔍 Botón Rechazar existe:", $("#btnRechazar").length > 0);
    
    // USAR DELEGACIÓN DE EVENTOS - MÁS CONFIABLE
    $(document).on("click", "#btnAprobar", function(e) {
        e.preventDefault();
        console.log("🟢 Click en botón APROBAR");
        
        var comentarios = $("#comentarios").val().trim();
        console.log("📝 Comentarios:", comentarios);
        
        if (comentarios === "") {
            $("#comentarios").addClass("is-invalid");
            mostrarToast("Debe ingresar un comentario", "warning");
            return;
        }
        
        if (!confirm("¿Estás seguro de aprobar esta solicitud?")) {
            return;
        }
        
        aprobarSolicitud();
    });
    
    $(document).on("click", "#btnRechazar", function(e) {
        e.preventDefault();
        console.log("🔴 Click en botón RECHAZAR");
        
        var comentarios = $("#comentarios").val().trim();
        console.log("📝 Comentarios:", comentarios);
        
        if (comentarios === "") {
            $("#comentarios").addClass("is-invalid");
            mostrarToast("Debe ingresar un comentario para rechazar", "warning");
            return;
        }
        
        if (!confirm("¿Estás seguro de rechazar esta solicitud?")) {
            return;
        }
        
        rechazarSolicitud();
    });
    
    $("#comentarios").on("input", function() {
        $(this).removeClass("is-invalid");
    });
    
    // Inicializar DataTable si existe
    if ($.fn.DataTable && $("#tablaSkusAprobacion").length) {
        $("#tablaSkusAprobacion").DataTable({ 
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }, 
            pageLength: 10, 
            ordering: false 
        });
    }
});

function aprobarSolicitud() {
    console.log("📤 Enviando APROBACIÓN...");
    
    var comentarios = $("#comentarios").val().trim();
    var fechaTermino = $("#fechaTermino").val();
    
    const datos = { 
        id_solicitud: ID_SOLICITUD, 
        comentarios: comentarios, 
        fecha_termino: fechaTermino, 
        csrf_token: CSRF_TOKEN 
    };
    
    console.log("📤 Datos a enviar:", datos);
    
    $("#btnAprobar").prop("disabled", true).html(\'<span class="spinner-border spinner-border-sm me-2"></span>Procesando...\');
    
    $.ajax({
        url: BASE_URL + "public/index.php?route=aprobaciones/aprobar", 
        method: "POST", 
        data: JSON.stringify(datos), 
        contentType: "application/json",
        success: function(response) {
            console.log("📥 Respuesta aprobar:", response);
            if (response.success) { 
                mostrarToast(response.message, "success"); 
                setTimeout(function() {
                    window.location.href = BASE_URL + "public/index.php?route=aprobaciones/mis-aprobaciones";
                }, 2000); 
            } else { 
                mostrarToast(response.message, "danger"); 
                $("#btnAprobar").prop("disabled", false).html(\'<i class="fas fa-check me-2"></i>Aprobar\'); 
            }
        }, 
        error: function(xhr) {
            console.log("❌ Error AJAX aprobar:", xhr);
            try {
                var response = JSON.parse(xhr.responseText);
                mostrarToast(response.message || "Error al procesar", "danger");
            } catch(e) {
                mostrarToast("Error al procesar la solicitud", "danger");
            }
            $("#btnAprobar").prop("disabled", false).html(\'<i class="fas fa-check me-2"></i>Aprobar\'); 
        }
    });
}

function rechazarSolicitud() {
    console.log("📤 Enviando RECHAZO...");
    
    var comentarios = $("#comentarios").val().trim();
    
    const datos = { 
        id_solicitud: ID_SOLICITUD, 
        comentarios: comentarios, 
        csrf_token: CSRF_TOKEN 
    };
    
    console.log("📤 Datos a enviar:", datos);
    
    $("#btnRechazar").prop("disabled", true).html(\'<span class="spinner-border spinner-border-sm me-2"></span>Procesando...\');
    
    $.ajax({
        url: BASE_URL + "public/index.php?route=aprobaciones/rechazar", 
        method: "POST", 
        data: JSON.stringify(datos), 
        contentType: "application/json",
        success: function(response) {
            console.log("📥 Respuesta rechazar:", response);
            if (response.success) { 
                mostrarToast(response.message, "danger"); 
                setTimeout(function() {
                    window.location.href = BASE_URL + "public/index.php?route=aprobaciones/mis-aprobaciones";
                }, 2000); 
            } else { 
                mostrarToast(response.message, "danger"); 
                $("#btnRechazar").prop("disabled", false).html(\'<i class="fas fa-times me-2"></i>Rechazar\'); 
            }
        }, 
        error: function(xhr) {
            console.log("❌ Error AJAX rechazar:", xhr);
            try {
                var response = JSON.parse(xhr.responseText);
                mostrarToast(response.message || "Error al procesar", "danger");
            } catch(e) {
                mostrarToast("Error al procesar la solicitud", "danger");
            }
            $("#btnRechazar").prop("disabled", false).html(\'<i class="fas fa-times me-2"></i>Rechazar\'); 
        }
    });
}
</script>
';
?>
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tasks me-2"></i>Detalle de Aprobación</h1>
        <div>
            <span class="badge bg-<?php echo estadoClase($solicitud['estado']); ?> fs-6">
                <?php echo ucfirst(str_replace('_', ' ', $solicitud['estado'])); ?>
            </span>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=aprobaciones/mis-aprobaciones" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h5></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Código</dt>
                        <dd class="col-sm-8"><strong><?php echo $solicitud['codigo_solicitud']; ?></strong></dd>
                        <dt class="col-sm-4">Cliente</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($solicitud['nombre_cliente']); ?></dd>
                        <dt class="col-sm-4">Vendedor</dt>
                        <dd class="col-sm-8"><?php echo $solicitud['email_vendedor']; ?></dd>
                        <dt class="col-sm-4">Fecha Inicio</dt>
                        <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($solicitud['fecha_inicio'])); ?></dd>
                        <dt class="col-sm-4">Fecha Término</dt>
                        <dd class="col-sm-8">
                            <?php if (isset($aprobacion['estado']) && $aprobacion['estado'] === 'pendiente'): ?>
                                <input type="date" class="form-control" id="fechaTermino" value="<?php echo $solicitud['fecha_termino']; ?>">
                                <small class="text-muted">Puede modificar la fecha</small>
                            <?php else: ?>
                                <?php echo date('d/m/Y', strtotime($solicitud['fecha_termino'])); ?>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen</h5></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <div class="display-6"><?php echo count($solicitud['detalles']); ?></div>
                                <small>SKUs</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <div class="display-6"><?php echo number_format($solicitud['descuento_promedio'] ?? 0, 1, ',', '.'); ?>%</div>
                                <small>Desc. Prom.</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <div class="display-6"><?php echo count($solicitud['detalles'] ?? []); ?></div>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($aprobacion['estado']) && $aprobacion['estado'] === 'pendiente'): ?>
                        <hr>
                        <div class="mt-3">
                            <label class="form-label fw-bold">Comentarios <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="comentarios" rows="3" placeholder="Ingrese comentarios obligatorios" style="resize: vertical;"></textarea>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                <button class="btn btn-success btn-lg" id="btnAprobar" style="min-width: 120px;">
                                    <i class="fas fa-check me-2"></i>Aprobar
                                </button>
                                <button class="btn btn-danger btn-lg" id="btnRechazar" style="min-width: 120px;">
                                    <i class="fas fa-times me-2"></i>Rechazar
                                </button>
                            </div>
                        </div>
                    <?php elseif (isset($aprobacion['estado']) && $aprobacion['estado'] === 'aprobada'): ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>✅ Solicitud aprobada</strong>
                            <?php if (!empty($aprobacion['comentarios'])): ?>
                                <p class="mb-0 mt-1"><strong>Comentarios:</strong> <?php echo nl2br(htmlspecialchars($aprobacion['comentarios'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php elseif (isset($aprobacion['estado']) && $aprobacion['estado'] === 'rechazada'): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>❌ Solicitud rechazada</strong>
                            <?php if (!empty($aprobacion['comentarios'])): ?>
                                <p class="mb-0 mt-1"><strong>Comentarios:</strong> <?php echo nl2br(htmlspecialchars($aprobacion['comentarios'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Esta solicitud ya fue procesada o no requiere tu aprobación.</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Detalle de SKUs</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaSkusAprobacion">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Material</th>
                                    <th>Familia</th>
                                    <th>Unidad</th>
                                    <th>Precio Lista</th>
                                    <th>Precio Prop.</th>
                                    <th>% Desc.</th>
                                    <th>Volumen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitud['detalles'] as $detalle): ?>
                                <tr>
                                    <td><strong><?php echo $detalle['sku']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($detalle['nombre_material']); ?></td>
                                    <td><?php echo $detalle['familia']; ?></td>
                                    <td><?php echo $detalle['unidad_carga']; ?></td>
                                    <td><?php echo number_format($detalle['precio_lista'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($detalle['precio_propuesto'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $detalle['porcentaje_descuento'] > 10 ? 'warning' : 'info'; ?>">
                                            <?php echo number_format($detalle['porcentaje_descuento'], 1, ',', '.'); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo number_format($detalle['volumen_esperado'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>