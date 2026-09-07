<?php
$titulo = 'Gestión de Materiales';
$scripts = '
<script>
$(document).ready(function() {
    // Inicializar DataTable solo si hay datos
    var tbody = $("#tablaMateriales tbody");
    var hasData = false;
    
    tbody.find("tr").each(function() {
        if (!$(this).find("td[colspan]").length) {
            hasData = true;
        }
    });
    
    if (hasData) {
        try {
            $("#tablaMateriales").DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                order: [[2, "asc"]],
                pageLength: 25,
                dom: "<\"row\"<\"col-sm-6\"l><\"col-sm-6\"f>>rtip",
                autoWidth: false
            });
        } catch(e) {
            console.log("Error al inicializar DataTable:", e);
        }
    } else {
        if ($.fn.DataTable.isDataTable("#tablaMateriales")) {
            $("#tablaMateriales").DataTable().destroy();
        }
    }
});

function editarMaterial(id, sku, nombre, familia, unidad_carga) {
    $("#materialId").val(id);
    $("#materialSku").val(sku);
    $("#materialNombre").val(nombre);
    $("#materialFamilia").val(familia);
    $("#materialUnidad").val(unidad_carga);
    $("#modalMaterialLabel").html("<i class=\"fas fa-edit me-2\"></i>Editar Material");
    $("#btnGuardarMaterial").html("<i class=\"fas fa-save me-1\"></i>Actualizar");
    $("#materialAccion").val("editar");
    new bootstrap.Modal(document.getElementById("modalMaterial")).show();
}

function eliminarMaterial(id, sku) {
    if (confirm("¿Está seguro de eliminar el material con SKU: " + sku + "?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "' . BASE_URL . 'public/index.php?route=admin/materiales";
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="' . Session::generarTokenCSRF() . '">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function limpiarFormulario() {
    $("#materialId").val("");
    $("#materialSku").val("");
    $("#materialNombre").val("");
    $("#materialFamilia").val("");
    $("#materialUnidad").val("");
    $("#materialAccion").val("agregar");
    $("#modalMaterialLabel").html(\'<i class="fas fa-box me-2"></i>Agregar Material\');
    $("#btnGuardarMaterial").html(\'<i class="fas fa-save me-1"></i>Guardar\');
}
</script>
';
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-boxes me-2"></i>Gestión de Materiales</h1>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMaterial" onclick="limpiarFormulario()">
                <i class="fas fa-plus me-2"></i>Agregar Material
            </button>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- Mostrar mensajes de sesión -->
    <?php
    $mensaje = Session::getMensaje();
    if (!empty($mensaje['mensaje'])):
    ?>
    <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $mensaje['tipo'] === 'success' ? 'check-circle' : ($mensaje['tipo'] === 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
        <?php echo htmlspecialchars($mensaje['mensaje']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaMateriales">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nombre</th>
                            <th>Familia</th>
                            <th>Unidad de Carga</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($materiales) && count($materiales) > 0): ?>
                            <?php foreach ($materiales as $material): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($material['sku']); ?></strong></td>
                                <td><?php echo htmlspecialchars($material['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($material['familia'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($material['unidad_carga'] ?? 'UND'); ?></td>
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x d-block mb-2"></i>
                                    No hay materiales registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Material -->
<div class="modal fade" id="modalMaterial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>public/index.php?route=admin/materiales">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalMaterialLabel">
                        <i class="fas fa-box me-2"></i>Agregar Material
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="agregar" id="materialAccion">
                    <input type="hidden" name="id" id="materialId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sku" id="materialSku" placeholder="Ej: LEC001" required>
                            <small class="text-muted">Código único del material</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="materialNombre" placeholder="Ej: Leche Entera 1L" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Familia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="familia" id="materialFamilia" placeholder="Ej: Lácteos" required>
                            <small class="text-muted">Agrupa materiales similares</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unidad de Carga <span class="text-danger">*</span></label>
                            <select class="form-select" name="unidad_carga" id="materialUnidad" required>
                                <option value="">Seleccione...</option>
                                <option value="UND">Unidad (UND)</option>
                                <option value="KG">Kilogramo (KG)</option>
                                <option value="LT">Litro (LT)</option>
                                <option value="Caja">Caja</option>
                                <option value="Pack">Pack</option>
                                <option value="Pallet">Pallet</option>
                            </select>
                            <small class="text-muted">Unidad de medida del producto</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarMaterial">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>