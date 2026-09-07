<?php
$titulo = 'Configuración del Sistema';
$scripts = '
<script>
$(document).ready(function() {
    // Configuración de idioma en español
    const dtLanguage = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sSearch": "Buscar:",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        }
    };

    // FUNCIÓN SEGURA PARA INICIALIZAR DATATABLES
    function initDataTable(tableId, orderableColumns = []) {
        var table = $(tableId);
        var tbody = table.find("tbody");
        var hasData = false;
        
        tbody.find("tr").each(function() {
            if (!$(this).find("td[colspan]").length) {
                hasData = true;
            }
        });
        
        if (hasData) {
            try {
                var config = {
                    language: dtLanguage,
                    pageLength: 10,
                    deferRender: true,
                    autoWidth: false
                };
                
                if (orderableColumns.length > 0) {
                    config.columnDefs = orderableColumns.map(col => ({ targets: col, orderable: false }));
                }
                
                return $(tableId).DataTable(config);
            } catch(e) {
                console.log("Error al inicializar " + tableId + ":", e);
                return null;
            }
        } else {
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }
            $(tableId + "_wrapper").remove();
            $(tableId).after(\'<div class="alert alert-info text-center py-3"><i class="fas fa-info-circle me-2"></i>No hay registros disponibles</div>\');
            return null;
        }
    }

    // Inicializar cada tabla
    initDataTable("#tablaUsuarios", [5]);
    initDataTable("#tablaTramos", [6]);
    initDataTable("#tablaAprobadores", [5]);
    initDataTable("#tablaClientes", [5]);
    initDataTable("#tablaMateriales", [4]);
    initDataTable("#tablaPrecios", [6]);
});
</script>
';
?>

<div class="fade-in">
    <h1 class="mb-4"><i class="fas fa-cog me-2"></i>Configuración del Sistema</h1>
    
    <!-- Tabs de configuración -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <!-- Tramos -->
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tramos">
                <i class="fas fa-layer-group me-2"></i>Tramos
            </a>
        </li>
        <!-- Aprobadores -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#aprobadores">
                <i class="fas fa-user-check me-2"></i>Aprobadores
            </a>
        </li>
        <!-- Usuarios -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#usuarios">
                <i class="fas fa-users me-2"></i>Usuarios
            </a>
        </li>
        <!--Clientes -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#clientes">
                <i class="fas fa-building me-2"></i>Clientes
            </a>
        </li>
        <!--Materiales -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#materiales">
                <i class="fas fa-boxes me-2"></i>Materiales
            </a>
        </li>
        <!--Precios -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#precios">
                <i class="fas fa-dollar-sign me-2"></i>Precios
            </a>
        </li>
    </ul>
    
    <div class="tab-content">
        
        <!-- ========================================== -->
        <!-- TAB 1: TRAMOS -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="tramos">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Configuración de Tramos</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTramo">
                        <i class="fas fa-plus me-1"></i>Agregar Tramo
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaTramos">
                            <thead>
                                <tr>
                                    <th>Canal</th>
                                    <th>Familia</th>
                                    <th>Tramo</th>
                                    <th>Descuento Min</th>
                                    <th>Descuento Max</th>
                                    <th>Aprobación Automática</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tramos)): ?>
                                    <?php foreach ($tramos as $tramo): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tramo['canal']); ?></td>
                                        <td><?php echo htmlspecialchars($tramo['familia'] ?? 'Todas'); ?></td>
                                        <td><?php echo (int)$tramo['tramo']; ?></td>
                                        <td><?php echo number_format($tramo['descuento_min'], 1, ',', '.'); ?>%</td>
                                        <td><?php echo number_format($tramo['descuento_max'], 1, ',', '.'); ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $tramo['aprobacion_automatica'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $tramo['aprobacion_automatica'] ? 'Sí' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                                                <input type="hidden" name="accion" value="eliminar_tramo">
                                                <input type="hidden" name="id" value="<?php echo (int)$tramo['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta configuración?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No hay tramos configurados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!-- TAB 2: APROBADORES -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="aprobadores">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Configuración de Aprobadores</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAprobador">
                        <i class="fas fa-plus me-1"></i>Agregar Aprobador
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaAprobadores">
                            <thead>
                                <tr>
                                    <th>Canal</th>
                                    <th>Tramo</th>
                                    <th>Aprobador</th>
                                    <th>Email</th>
                                    <th>Orden</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($aprobadores)): ?>
                                    <?php foreach ($aprobadores as $aprobador): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($aprobador['canal']); ?></td>
                                        <td><?php echo (int)$aprobador['tramo']; ?></td>
                                        <td><?php echo htmlspecialchars(($aprobador['nombre'] ?? '') . ' ' . ($aprobador['apellido'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($aprobador['email_aprobador']); ?></td>
                                        <td><?php echo (int)$aprobador['orden']; ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                                                <input type="hidden" name="accion" value="eliminar_aprobador">
                                                <input type="hidden" name="id" value="<?php echo (int)$aprobador['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta configuración?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No hay aprobadores configurados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!-- TAB 3: USUARIOS -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="usuarios">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Gestión de Usuarios</h5>
                    <a href="<?php echo BASE_URL; ?>public/index.php?route=auth/registrar" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Agregar Usuario
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaUsuarios">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Cargo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td><?php echo ucfirst($usuario['rol']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['cargo'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $usuario['activo'] ? 'success' : 'danger'; ?>">
                                                <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                                                <input type="hidden" name="accion" value="cambiar_estado_usuario">
                                                <input type="hidden" name="id" value="<?php echo (int)$usuario['id']; ?>">
                                                <input type="hidden" name="activo" value="<?php echo $usuario['activo'] ? 0 : 1; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $usuario['activo'] ? 'warning' : 'success'; ?>" onclick="return confirm('¿Cambiar estado del usuario?')">
                                                    <i class="fas fa-<?php echo $usuario['activo'] ? 'ban' : 'check'; ?>"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No hay usuarios registrados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!-- TAB 4: CLIENTES -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="clientes">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Gestión de Clientes</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCliente">
                            <i class="fas fa-plus me-1"></i>Agregar Cliente
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/clientes" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Ver Gestión Completa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaClientes">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Subcanal</th>
                                    <th>Canal</th>
                                    <th>Pagador</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($clientes)): ?>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($cliente['codigo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['subcanal']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['canal']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['pagador'] ?? '-'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editarCliente(<?php echo $cliente['id']; ?>, '<?php echo addslashes($cliente['codigo']); ?>', '<?php echo addslashes($cliente['nombre']); ?>', '<?php echo addslashes($cliente['subcanal']); ?>', '<?php echo addslashes($cliente['canal']); ?>', '<?php echo addslashes($cliente['pagador'] ?? ''); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(<?php echo $cliente['id']; ?>, '<?php echo addslashes($cliente['nombre']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No hay clientes registrados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!--TAB 5: MATERIALES -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="materiales">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Gestión de Materiales</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMaterial">
                            <i class="fas fa-plus me-1"></i>Agregar Material
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/materiales" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Ver Gestión Completa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaMateriales">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Nombre</th>
                                    <th>Familia</th>
                                    <th>Unidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($materiales)): ?>
                                    <?php foreach ($materiales as $material): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($material['sku']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($material['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($material['familia']); ?></td>
                                        <td><?php echo htmlspecialchars($material['unidad_carga']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editarMaterial(<?php echo $material['id']; ?>, '<?php echo addslashes($material['sku']); ?>', '<?php echo addslashes($material['nombre']); ?>', '<?php echo addslashes($material['familia']); ?>', '<?php echo addslashes($material['unidad_carga']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarMaterial(<?php echo $material['id']; ?>, '<?php echo addslashes($material['sku']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No hay materiales registrados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ========================================== -->
        <!--TAB 6: PRECIOS -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="precios">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Gestión de Precios</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPrecio">
                            <i class="fas fa-plus me-1"></i>Agregar Precio
                        </button>
                        <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/precios" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Ver Gestión Completa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaPrecios">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Material</th>
                                    <th>Subcanal</th>
                                    <th>Precio Lista</th>
                                    <th>Vigencia</th>
                                    <th>Término</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($precios)): ?>
                                    <?php foreach ($precios as $precio): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($precio['sku']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($precio['nombre_material'] ?? $precio['sku']); ?></td>
                                        <td><?php echo htmlspecialchars($precio['subcanal']); ?></td>
                                        <td>$<?php echo number_format($precio['precio_lista'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($precio['fecha_vigencia'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($precio['fecha_termino'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editarPrecio(<?php echo $precio['id']; ?>, '<?php echo addslashes($precio['sku']); ?>', '<?php echo $precio['precio_lista']; ?>', '<?php echo $precio['fecha_vigencia']; ?>', '<?php echo $precio['fecha_termino']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarPrecio(<?php echo $precio['id']; ?>, '<?php echo addslashes($precio['sku']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No hay precios registrados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div> <!-- Fin tab-content -->
</div>

<!-- ========================================== -->
<!-- MODALES -->
<!-- ========================================== -->

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>public/index.php?route=admin/clientes">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="id" id="clienteId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="codigo" id="clienteCodigo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="clienteNombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subcanal <span class="text-danger">*</span></label>
                            <select class="form-select" name="subcanal" id="clienteSubcanal" required>
                                <option value="">Seleccione...</option>
                                <option value="Retail">Retail</option>
                                <option value="Distribución">Distribución</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Canal <span class="text-danger">*</span></label>
                            <select class="form-select" name="canal" id="clienteCanal" required>
                                <option value="">Seleccione...</option>
                                <option value="Cobertura Nacional">Cobertura Nacional</option>
                                <option value="Retail">Retail</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Pagador</label>
                            <input type="text" class="form-control" name="pagador" id="clientePagador" placeholder="Opcional">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Material -->
<div class="modal fade" id="modalMaterial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>public/index.php?route=admin/materiales">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-box me-2"></i>Agregar Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="id" id="materialId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sku" id="materialSku" required>
                            <small class="text-muted">Código único del material</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="materialNombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Familia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="familia" id="materialFamilia" required>
                            <small class="text-muted">Ej: Bebidas, Lácteos, Snacks</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unidad de Carga <span class="text-danger">*</span></label>
                            <select class="form-select" name="unidad_carga" id="materialUnidad" required>
                                <option value="">Seleccione...</option>
                                <option value="Caja">Caja</option>
                                <option value="Pack">Pack</option>
                                <option value="Unidad">Unidad</option>
                                <option value="Pallet">Pallet</option>
                                <option value="Litro">Litro</option>
                                <option value="Kilo">Kilo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Material</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Precio -->
<div class="modal fade" id="modalPrecio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>public/index.php?route=admin/precios">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tag me-2"></i>Agregar Precio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="id" id="precioId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                            <select class="form-select" name="sku" id="precioSku" required>
                                <option value="">Seleccione...</option>
                                <?php if (!empty($materiales)): ?>
                                    <?php foreach ($materiales as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['sku']); ?>">
                                            <?php echo htmlspecialchars($material['sku'] . ' - ' . $material['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subcanal <span class="text-danger">*</span></label>
                            <select class="form-select" name="subcanal" id="precioSubcanal" required>
                                <option value="">Seleccione...</option>
                                <option value="Retail">Retail</option>
                                <option value="Distribución">Distribución</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio Lista (CLP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="precio_lista" id="precioLista" required min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Vigencia <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_vigencia" id="precioVigencia" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Término <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_termino" id="precioTermino" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Precio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de Tramos y Aprobadores -->
<?php include __DIR__ . '/modales.php'; ?>

<!-- ========================================== -->
<!-- SCRIPTS PARA FUNCIONES JS -->
<!-- ========================================== -->
<script>
// Funciones para Clientes
function editarCliente(id, codigo, nombre, subcanal, canal, pagador) {
    $("#clienteId").val(id);
    $("#clienteCodigo").val(codigo);
    $("#clienteNombre").val(nombre);
    $("#clienteSubcanal").val(subcanal);
    $("#clienteCanal").val(canal);
    $("#clientePagador").val(pagador);
    $("#modalClienteLabel").text("Editar Cliente");
    $("#btnGuardarCliente").html('<i class="fas fa-save me-1"></i>Actualizar');
    $('input[name="accion"]').val("editar");
    new bootstrap.Modal(document.getElementById("modalCliente")).show();
}

function eliminarCliente(id, nombre) {
    if (confirm("¿Está seguro de eliminar el cliente: " + nombre + "?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "<?php echo BASE_URL; ?>public/index.php?route=admin/clientes";
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Funciones para Materiales
function editarMaterial(id, sku, nombre, familia, unidad_carga) {
    $("#materialId").val(id);
    $("#materialSku").val(sku);
    $("#materialNombre").val(nombre);
    $("#materialFamilia").val(familia);
    $("#materialUnidad").val(unidad_carga);
    $("#modalMaterialLabel").text("Editar Material");
    $("#btnGuardarMaterial").html('<i class="fas fa-save me-1"></i>Actualizar');
    $('input[name="accion"]').val("editar");
    new bootstrap.Modal(document.getElementById("modalMaterial")).show();
}

function eliminarMaterial(id, sku) {
    if (confirm("¿Está seguro de eliminar el material con SKU: " + sku + "?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "<?php echo BASE_URL; ?>public/index.php?route=admin/materiales";
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Funciones para Precios
function editarPrecio(id, sku, precio_lista, fecha_vigencia, fecha_termino) {
    $("#precioId").val(id);
    $("#precioSku").val(sku);
    $("#precioLista").val(precio_lista);
    $("#precioVigencia").val(fecha_vigencia);
    $("#precioTermino").val(fecha_termino);
    $("#modalPrecioLabel").text("Editar Precio");
    $("#btnGuardarPrecio").html('<i class="fas fa-save me-1"></i>Actualizar');
    $('input[name="accion"]').val("editar");
    $("#precioSku").prop("disabled", true);
    new bootstrap.Modal(document.getElementById("modalPrecio")).show();
}

function eliminarPrecio(id, sku) {
    if (confirm("¿Está seguro de eliminar el precio del SKU: " + sku + "?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "<?php echo BASE_URL; ?>public/index.php?route=admin/precios";
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Resetear modales al cerrar
document.addEventListener('hidden.bs.modal', function (event) {
    // Clientes
    if (event.target.id === 'modalCliente') {
        $("#clienteId").val("");
        $("#clienteCodigo").val("");
        $("#clienteNombre").val("");
        $("#clienteSubcanal").val("");
        $("#clienteCanal").val("");
        $("#clientePagador").val("");
        $('input[name="accion"]').val("agregar");
        $("#modalClienteLabel").text("Agregar Cliente");
        $("#btnGuardarCliente").html('<i class="fas fa-save me-1"></i>Guardar');
    }
    // Materiales
    if (event.target.id === 'modalMaterial') {
        $("#materialId").val("");
        $("#materialSku").val("");
        $("#materialNombre").val("");
        $("#materialFamilia").val("");
        $("#materialUnidad").val("");
        $('input[name="accion"]').val("agregar");
        $("#modalMaterialLabel").text("Agregar Material");
        $("#btnGuardarMaterial").html('<i class="fas fa-save me-1"></i>Guardar');
    }
    // Precios
    if (event.target.id === 'modalPrecio') {
        $("#precioId").val("");
        $("#precioSku").val("");
        $("#precioLista").val("");
        $("#precioVigencia").val("");
        $("#precioTermino").val("");
        $('input[name="accion"]').val("agregar");
        $("#modalPrecioLabel").text("Agregar Precio");
        $("#btnGuardarPrecio").html('<i class="fas fa-save me-1"></i>Guardar');
        $("#precioSku").prop("disabled", false);
    }
});
</script>