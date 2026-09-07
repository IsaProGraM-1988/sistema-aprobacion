<?php
$titulo = 'Mis Aprobaciones';
$scripts = '
<script>
$(document).ready(function() {
    var dtLanguage = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningun dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sSearch": "Buscar:",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Ultimo",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        }
    };

    var tbody = $("#tablaAprobaciones tbody");
    var hasData = false;
    
    tbody.find("tr").each(function() {
        if (!$(this).find("td[colspan]").length) {
            hasData = true;
        }
    });
    
    if (hasData) {
        try {
            $("#tablaAprobaciones").DataTable({
                language: dtLanguage,
                order: [[4, "desc"]],
                pageLength: 10,
                columnDefs: [
                    { targets: [6], orderable: false }
                ],
                autoWidth: false
            });
        } catch(e) {
            console.log("Error al inicializar DataTable:", e);
        }
    } else {
        if ($.fn.DataTable.isDataTable("#tablaAprobaciones")) {
            $("#tablaAprobaciones").DataTable().destroy();
        }
        $("#tablaAprobaciones_wrapper").remove();
        $("#tablaAprobaciones").after(\'<div class="alert alert-info text-center py-3"><i class="fas fa-info-circle me-2"></i>No hay aprobaciones registradas</div>\');
    }
});
</script>
';
?>
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tasks me-2"></i>Mis Aprobaciones</h1>
        <span class="badge bg-primary"><?php echo isset($aprobaciones) ? count($aprobaciones) : 0; ?> registros</span>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaAprobaciones">
                    <thead>
                        <tr>
                            <th>Codigo Solicitud</th>
                            <th>Cliente</th>
                            <th>SKUs</th>
                            <th>Descuento Prom.</th>
                            <th>Fecha Creacion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($aprobaciones) && is_array($aprobaciones) && count($aprobaciones) > 0): ?>
                            <?php foreach ($aprobaciones as $aprobacion):
                                $estadoClass = 'secondary';
                                $estadoTexto = $aprobacion['estado_aprobacion'] ?? 'pendiente';
                                
                                if ($estadoTexto == 'pendiente') {
                                    $estadoClass = 'warning';
                                    $estadoTexto = 'Pendiente';
                                } elseif ($estadoTexto == 'aprobada') {
                                    $estadoClass = 'success';
                                    $estadoTexto = 'Aprobada';
                                } elseif ($estadoTexto == 'rechazada') {
                                    $estadoClass = 'danger';
                                    $estadoTexto = 'Rechazada';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo isset($aprobacion['codigo_solicitud']) ? $aprobacion['codigo_solicitud'] : 'N/A'; ?></strong></td>
                                <td><?php echo isset($aprobacion['nombre_cliente']) ? htmlspecialchars($aprobacion['nombre_cliente']) : 'N/A'; ?></td>
                                <td><?php echo isset($aprobacion['total_skus']) ? $aprobacion['total_skus'] : 0; ?></td>
                                <td><?php echo isset($aprobacion['descuento_promedio']) ? number_format($aprobacion['descuento_promedio'], 1, ',', '.') : '0.0'; ?>%</td>
                                <td><?php echo isset($aprobacion['fecha_creacion']) ? date('d/m/Y H:i', strtotime($aprobacion['fecha_creacion'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $estadoClass; ?>">
                                        <?php echo ucfirst($estadoTexto); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($aprobacion['estado_aprobacion']) && $aprobacion['estado_aprobacion'] == 'pendiente'): ?>
                                        <a href="<?php echo BASE_URL; ?>public/index.php?route=aprobaciones/detalle/<?php echo isset($aprobacion['id_solicitud']) ? $aprobacion['id_solicitud'] : 0; ?>"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Revisar
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/detalle/<?php echo isset($aprobacion['id_solicitud']) ? $aprobacion['id_solicitud'] : 0; ?>"
                                           class="btn btn-sm btn-secondary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay aprobaciones registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>