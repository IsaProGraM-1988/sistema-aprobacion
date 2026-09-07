<?php
$titulo = 'Todas las Solicitudes';
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

        var tbody = $("#tablaTodasSolicitudes tbody");
        var hasData = false;
        
        tbody.find("tr").each(function() {
            if (!$(this).find("td[colspan]").length) {
                hasData = true;
            }
        });
        
        if (hasData) {
            try {
                $("#tablaTodasSolicitudes").DataTable({
                    language: dtLanguage,
                    order: [[5, "desc"]],
                    pageLength: 10,
                    columnDefs: [
                        { targets: [7], orderable: false }
                    ],
                    autoWidth: false
                });
            } catch(e) {
                console.log("Error al inicializar DataTable:", e);
            }
        } else {
            if ($.fn.DataTable.isDataTable("#tablaTodasSolicitudes")) {
                $("#tablaTodasSolicitudes").DataTable().destroy();
            }
            $("#tablaTodasSolicitudes_wrapper").remove();
            $("#tablaTodasSolicitudes").after(\'<div class="alert alert-info text-center py-3"><i class="fas fa-info-circle me-2"></i>No hay solicitudes registradas</div>\');
        }
    });
</script>
';
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-list me-2"></i>Todas las Solicitudes</h1>
        <div>
            <span class="badge bg-info me-2">Total: <?php echo count($solicitudes); ?></span>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="btn btn-secondary btn-sm">
                <i class="fas fa-cog me-1"></i>Configuracion
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaTodasSolicitudes">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>SKUs</th>
                            <th>Descuento</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($solicitudes)): ?>
                            <?php foreach ($solicitudes as $solicitud): 
                                $estadoClass = 'secondary';
                                $estadoTexto = $solicitud['estado'];
                                
                                if ($solicitud['estado'] == 'borrador') {
                                    $estadoClass = 'secondary';
                                    $estadoTexto = 'Borrador';
                                } elseif ($solicitud['estado'] == 'enviada') {
                                    $estadoClass = 'info';
                                    $estadoTexto = 'Enviada';
                                } elseif ($solicitud['estado'] == 'en_aprobacion') {
                                    $estadoClass = 'warning';
                                    $estadoTexto = 'En Aprobacion';
                                } elseif ($solicitud['estado'] == 'aprobada') {
                                    $estadoClass = 'success';
                                    $estadoTexto = 'Aprobada';
                                } elseif ($solicitud['estado'] == 'aprobada_parcial') {
                                    $estadoClass = 'warning';
                                    $estadoTexto = 'Aprobada Parcial';
                                } elseif ($solicitud['estado'] == 'rechazada') {
                                    $estadoClass = 'danger';
                                    $estadoTexto = 'Rechazada';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo $solicitud['codigo_solicitud']; ?></strong></td>
                                <td>
                                    <?php 
                                    $nombreVendedor = trim(($solicitud['nombre_vendedor'] ?? '') . ' ' . ($solicitud['apellido_vendedor'] ?? ''));
                                    echo htmlspecialchars($nombreVendedor ?: $solicitud['email_vendedor']);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($solicitud['nombre_cliente']); ?></td>
                                <td><?php echo $solicitud['total_skus'] ?? 0; ?></td>
                                <td><?php echo number_format($solicitud['descuento_promedio'] ?? 0, 1, ',', '.'); ?>%</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_creacion'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $estadoClass; ?>">
                                        <?php echo $estadoTexto; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/detalle/<?php echo $solicitud['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($solicitud['estado'] == 'borrador'): ?>
                                        <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/editar/<?php echo $solicitud['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No hay solicitudes registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>