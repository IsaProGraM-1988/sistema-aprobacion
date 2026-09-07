<?php
$titulo = 'Dashboard';
$scripts = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // ============================================================
    // VERIFICAR QUE EXISTAN DATOS ANTES DE CREAR GRÁFICOS
    // ============================================================
    var hayDatos = false;
    var totalSolicitudes = ' . ($estadisticas['total'] ?? 0) . ';
    
    if (totalSolicitudes > 0) {
        hayDatos = true;
    }

    // ============================================================
    // GRÁFICO DE ESTADOS (Doughnut) - SOLO SI HAY DATOS
    // ============================================================
    var ctxEstados = document.getElementById("graficoEstados");
    if (ctxEstados && hayDatos) {
        new Chart(ctxEstados.getContext("2d"), {
            type: "doughnut",
            data: {
                labels: ["Enviadas", "En Aprobación", "Aprobadas", "Rechazadas", "Borradores"],
                datasets: [{
                    data: [
                        ' . ($estadisticas['enviadas'] ?? 0) . ', 
                        ' . ($estadisticas['en_aprobacion'] ?? 0) . ', 
                        ' . ($estadisticas['aprobadas'] ?? 0) . ', 
                        ' . ($estadisticas['rechazadas'] ?? 0) . ', 
                        ' . ($estadisticas['borradores'] ?? 0) . '
                    ],
                    backgroundColor: ["#17a2b8", "#ffc107", "#28a745", "#dc3545", "#6c757d"],
                    borderColor: ["#117a8b", "#d39e00", "#1e7e34", "#bd2130", "#5a6268"],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: "circle",
                            font: { size: 11 }
                        }
                    }
                },
                cutout: "65%"
            }
        });
    } else if (ctxEstados && !hayDatos) {
        // Mostrar mensaje si no hay datos
        ctxEstados.parentElement.innerHTML = \'<div class="text-center text-muted py-4"><i class="fas fa-inbox fa-3x d-block mb-2" style="color: #dee2e6;"></i><p class="mb-0">No hay datos para mostrar</p></div>\';
    }

    // ============================================================
    // GRÁFICO DE SOLICITUDES MENSUALES (Barra) - SOLO SI HAY DATOS
    // ============================================================
    var ctxMensual = document.getElementById("graficoMensual");
    var meses = ' . json_encode($solicitudesMensuales['meses'] ?? []) . ';
    var cantidades = ' . json_encode($solicitudesMensuales['cantidades'] ?? []) . ';
    
    if (ctxMensual && meses.length > 0 && cantidades.length > 0) {
        new Chart(ctxMensual.getContext("2d"), {
            type: "bar",
            data: {
                labels: meses,
                datasets: [{
                    label: "Solicitudes",
                    data: cantidades,
                    backgroundColor: ["#2d6da8", "#3a7fc4", "#4a8fd4", "#5a9fe4", "#6aaff4", "#7abff4"],
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 10 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    } else if (ctxMensual && (meses.length === 0 || cantidades.length === 0)) {
        ctxMensual.parentElement.innerHTML = \'<div class="text-center text-muted py-4"><i class="fas fa-chart-line fa-3x d-block mb-2" style="color: #dee2e6;"></i><p class="mb-0">No hay datos mensuales disponibles</p></div>\';
    }

    // ============================================================
    // INICIALIZAR DATATABLE CON IDIOMA - SOLO SI HAY FILAS
    // ============================================================
    var tabla = $("#tablaUltimasSolicitudes");
    if (tabla.find("tbody tr").length > 0) {
        try {
            tabla.DataTable({
                language: {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible",
                    "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de _MAX_ registros)",
                    "sSearch": "Buscar:",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    }
                },
                pageLength: 5,
                ordering: true,
                order: [[4, "desc"]],
                columnDefs: [
                    { targets: [6], orderable: false }
                ],
                autoWidth: false,
                dom: "<\"row\"<\"col-sm-6\"l><\"col-sm-6\"f>>rtip"
            });
        } catch(e) {
            console.log("Error al inicializar DataTable:", e);
        }
    }
});
</script>
';
?>

<!-- ============================================================ -->
<!-- DASHBOARD REDISEÑADO - SIN BARRA SUPERIOR -->
<!-- ============================================================ -->
<div class="dashboard-container">

    <!-- ========================================================== -->
    <!-- ENCABEZADO - SIN BARRA DE USUARIO -->
    <!-- ========================================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0" style="color: #1a2a3a;">
                <i class="fas fa-th-large me-2" style="color: #2d6da8;"></i>Dashboard
            </h1>
            <p class="text-muted small mb-0">
                <i class="fas fa-user me-1"></i>
                <?php echo htmlspecialchars(Session::get('nombre') . ' ' . Session::get('apellido')); ?>
                <span class="mx-1">·</span>
                <span class="badge bg-light text-dark rounded-pill px-2 py-0"><?php echo ucfirst(Session::get('rol')); ?></span>
            </p>
        </div>
        <div>
            <span class="badge bg-primary rounded-pill px-3 py-2">
                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y'); ?>
            </span>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TARJETAS DE ESTADÍSTICAS - DISEÑO MODERNO -->
    <!-- ========================================================== -->
    <div class="row g-3 mb-4">
        <!-- Total -->
        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="stat-card-modern stat-card-total">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Solicitudes</div>
                </div>
                <div class="stat-trend">
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-chart-line me-1"></i>Todo
                    </span>
                </div>
            </div>
        </div>

        <!-- Enviadas -->
        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="stat-card-modern stat-card-enviadas">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['enviadas'] ?? 0; ?></div>
                    <div class="stat-label">Enviadas</div>
                </div>
                <div class="stat-trend">
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-clock me-1"></i>Pendientes
                    </span>
                </div>
            </div>
        </div>

        <!-- En Aprobación -->
        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="stat-card-modern stat-card-aprobacion">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['en_aprobacion'] ?? 0; ?></div>
                    <div class="stat-label">En Aprobación</div>
                </div>
                <div class="stat-trend">
                    <span class="badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-spinner fa-spin me-1"></i>Esperando
                    </span>
                </div>
            </div>
        </div>

        <!-- Aprobadas -->
        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="stat-card-modern stat-card-aprobadas">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['aprobadas'] ?? 0; ?></div>
                    <div class="stat-label">Aprobadas</div>
                </div>
                <div class="stat-trend">
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check me-1"></i>Completadas
                    </span>
                </div>
            </div>
        </div>

        <!-- Rechazadas -->
        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="stat-card-modern stat-card-rechazadas">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['rechazadas'] ?? 0; ?></div>
                    <div class="stat-label">Rechazadas</div>
                </div>
                <div class="stat-trend">
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-exclamation me-1"></i>Revisar
                    </span>
                </div>
            </div>
        </div>

        <!-- Borradores -->
        <div class="col-xl-2 col-lg-4 col-md-4 col-6">
            <div class="stat-card-modern stat-card-borradores">
                <div class="stat-icon">
                    <i class="fas fa-pencil-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['borradores'] ?? 0; ?></div>
                    <div class="stat-label">Borradores</div>
                </div>
                <div class="stat-trend">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-edit me-1"></i>Incompletos
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- GRÁFICOS - TAMAÑO EQUILIBRADO -->
    <!-- ========================================================== -->
    <div class="row g-4 mb-4">
        <div class="col-xl-5 col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-pie me-2" style="color: #2d6da8;"></i>
                        Distribución por Estado
                    </h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 260px; max-height: 300px;">
                    <canvas id="graficoEstados" style="max-width: 260px; max-height: 260px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-7 col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-bar me-2" style="color: #2d6da8;"></i>
                        Solicitudes por Mes
                    </h5>
                </div>
                <div class="card-body" style="min-height: 260px;">
                    <canvas id="graficoMensual" style="height: 200px !important; width: 100% !important;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- TABLA DE ÚLTIMAS SOLICITUDES -->
    <!-- ========================================================== -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-history me-2" style="color: #2d6da8;"></i>
                Últimas Solicitudes
            </h5>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/mis-solicitudes" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-arrow-right me-1"></i>Ver todas
            </a>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaUltimasSolicitudes">
                    <thead>
                        <tr>
                            <th class="fw-semibold" style="color: #1a2a3a;">Código</th>
                            <th class="fw-semibold" style="color: #1a2a3a;">Cliente</th>
                            <th class="fw-semibold text-center" style="color: #1a2a3a;">SKUs</th>
                            <th class="fw-semibold text-center" style="color: #1a2a3a;">Descuento</th>
                            <th class="fw-semibold" style="color: #1a2a3a;">Fecha</th>
                            <th class="fw-semibold text-center" style="color: #1a2a3a;">Estado</th>
                            <th class="fw-semibold text-center" style="color: #1a2a3a;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ultimasSolicitudes)): ?>
                            <?php foreach ($ultimasSolicitudes as $solicitud): ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold" style="color: #1a2a3a;">
                                        <?php echo $solicitud['codigo_solicitud']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($solicitud['nombre_cliente']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-1">
                                        <?php echo $solicitud['total_skus'] ?? 0; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold" style="color: #2d6da8;">
                                        <?php echo number_format($solicitud['descuento_promedio'] ?? 0, 1, ',', '.'); ?>%
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_creacion'])); ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo estadoClase($solicitud['estado']); ?> rounded-pill px-3 py-1">
                                        <?php echo ucfirst(str_replace('_', ' ', $solicitud['estado'])); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/detalle/<?php echo $solicitud['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary rounded-circle" 
                                       title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($solicitud['estado'] == 'borrador'): ?>
                                        <a href="<?php echo BASE_URL; ?>public/index.php?route=solicitudes/editar/<?php echo $solicitud['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning rounded-circle" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2" style="color: #dee2e6;"></i>
                                    No hay solicitudes registradas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ESTILOS ADICIONALES PARA DASHBOARD -->
<!-- ============================================================ -->
<style>
    /* ========================================================== */
    /* TARJETAS MODERNAS */
    /* ========================================================== */
    .stat-card-modern {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.2rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-card-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.10);
    }

    .stat-card-modern .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .stat-card-total .stat-icon { background: #e8f0fe; color: #2d6da8; }
    .stat-card-enviadas .stat-icon { background: #e3f4f8; color: #17a2b8; }
    .stat-card-aprobacion .stat-icon { background: #fff8e1; color: #ffc107; }
    .stat-card-aprobadas .stat-icon { background: #e8f5e9; color: #28a745; }
    .stat-card-rechazadas .stat-icon { background: #fce4ec; color: #dc3545; }
    .stat-card-borradores .stat-icon { background: #f5f5f5; color: #6c757d; }

    .stat-card-modern .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-card-modern .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.2;
        color: #1a2a3a;
    }

    .stat-card-modern .stat-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #8c9aa8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    .stat-card-modern .stat-trend {
        flex-shrink: 0;
        margin-left: 0.5rem;
    }

    .stat-card-modern .stat-trend .badge {
        font-weight: 500;
        font-size: 0.65rem;
        padding: 4px 10px;
        border-radius: 20px;
    }

    /* ========================================================== */
    /* TABLA */
    /* ========================================================== */
    .table thead th {
        border-bottom: 2px solid #e9ecef;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.8rem 0.8rem;
        background: transparent;
        color: #6c757d;
    }

    .table tbody td {
        padding: 0.7rem 0.8rem;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: background 0.2s ease;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    /* ========================================================== */
    /* CARD */
    /* ========================================================== */
    .card {
        border-radius: 14px;
        background: #ffffff;
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem 1.25rem 0.5rem 1.25rem;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* ========================================================== */
    /* RESPONSIVE */
    /* ========================================================== */
    @media (max-width: 576px) {
        .stat-card-modern {
            padding: 0.8rem 0.7rem;
            gap: 0.7rem;
        }
        .stat-card-modern .stat-icon {
            width: 38px;
            height: 38px;
            font-size: 1rem;
        }
        .stat-card-modern .stat-number {
            font-size: 1.3rem;
        }
        .stat-card-modern .stat-label {
            font-size: 0.6rem;
        }
        .stat-card-modern .stat-trend .badge {
            font-size: 0.5rem;
            padding: 2px 8px;
        }
        .card-body {
            padding: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .stat-card-modern .stat-trend .badge {
            display: none;
        }
    }
</style>