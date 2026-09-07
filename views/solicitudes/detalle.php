<?php
$titulo = 'Detalle de Solicitud';
$scripts = '
<script>
$(document).ready(function() {
    $("#tablaSkus").DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 10, ordering: false
    });
});
</script>
';
?>
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-alt me-2"></i>Detalle de Solicitud</h1>
        <div>
            <span class="badge bg-<?php echo estadoClase($solicitud['estado']); ?> fs-6">
                <?php echo ucfirst(str_replace('_', ' ', $solicitud['estado'])); ?>
            </span>
            <?php if ($solicitud['estado'] === 'borrador'): ?>
                <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/editar/<?php echo $solicitud['id']; ?>" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Editar</a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/mis-solicitudes" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Volver</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Código</dt><dd class="col-sm-8"><?php echo $solicitud['codigo_solicitud']; ?></dd>
                        <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8"><?php echo htmlspecialchars($solicitud['nombre_cliente']); ?></dd>
                        <dt class="col-sm-4">Subcanal</dt><dd class="col-sm-8"><?php echo $solicitud['subcanal']; ?></dd>
                        <dt class="col-sm-4">Canal</dt><dd class="col-sm-8"><?php echo $solicitud['canal']; ?></dd>
                        <dt class="col-sm-4">Vendedor</dt><dd class="col-sm-8"><?php echo $solicitud['email_vendedor']; ?></dd>
                        <dt class="col-sm-4">Fecha Inicio</dt><dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($solicitud['fecha_inicio'])); ?></dd>
                        <dt class="col-sm-4">Fecha Término</dt><dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($solicitud['fecha_termino'])); ?></dd>
                        <?php if (!empty($solicitud['motivo'])): ?>
                            <dt class="col-sm-4">Motivo</dt><dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($solicitud['motivo'])); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen</h5></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4"><div class="border rounded p-3"><div class="display-6"><?php echo count($solicitud['detalles']); ?></div><small class="text-muted">SKUs</small></div></div>
                        <div class="col-4"><div class="border rounded p-3"><div class="display-6"><?php echo number_format($solicitud['descuento_promedio'] ?? 0, 1, ',', '.'); ?>%</div><small class="text-muted">Desc. Prom.</small></div></div>
                        <div class="col-4"><div class="border rounded p-3"><div class="display-6"><?php echo count($solicitud['detalles']); ?></div><small class="text-muted">Total</small></div></div>
                    </div>
                    <?php if (!empty($solicitud['comentarios_aprobador'])): ?>
                        <div class="mt-3"><div class="alert alert-info"><strong><i class="fas fa-comment me-2"></i>Comentarios:</strong><p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($solicitud['comentarios_aprobador'])); ?></p></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Detalle de SKUs</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaSkus">
                            <thead><tr><th>SKU</th><th>Material</th><th>Familia</th><th>Unidad</th><th>Precio Lista</th><th>Precio Prop.</th><th>% Desc.</th><th>Volumen</th></tr></thead>
                            <tbody>
                                <?php foreach ($solicitud['detalles'] as $detalle): ?>
                                <tr>
                                    <td><strong><?php echo $detalle['sku']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($detalle['nombre_material']); ?></td>
                                    <td><?php echo $detalle['familia']; ?></td>
                                    <td><?php echo $detalle['unidad_carga']; ?></td>
                                    <td><?php echo number_format($detalle['precio_lista'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($detalle['precio_propuesto'], 0, ',', '.'); ?></td>
                                    <td><span class="badge bg-<?php echo $detalle['porcentaje_descuento'] > 10 ? 'warning' : 'info'; ?>"><?php echo number_format($detalle['porcentaje_descuento'], 1, ',', '.'); ?>%</span></td>
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