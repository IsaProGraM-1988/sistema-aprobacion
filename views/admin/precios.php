<?php
$titulo = 'Gestión de Precios';
$scripts = '<script>
$(document).ready(function() {
    // SOLO INICIALIZAR SI HAY DATOS REALES
    var tbody = $("#tablaPrecios tbody");
    var hasData = false;
    
    tbody.find("tr").each(function() {
        if (!$(this).find("td[colspan]").length) {
            hasData = true;
        }
    });
    
    if (hasData) {
        try {
            $("#tablaPrecios").DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                order: [[1, "asc"], [2, "asc"]],
                pageLength: 25,
                dom: "<\"row\"<\"col-sm-6\"l><\"col-sm-6\"f>>rtip",
                autoWidth: false
            });
        } catch(e) {
            console.log("Error al inicializar DataTable:", e);
        }
    } else {
        if ($.fn.DataTable.isDataTable("#tablaPrecios")) {
            $("#tablaPrecios").DataTable().destroy();
        }
        $("#tablaPrecios_wrapper").remove();
        $("#tablaPrecios").after(\'<div class="alert alert-info text-center py-3"><i class="fas fa-info-circle me-2"></i>No hay precios registrados</div>\');
    }
});

function editarPrecio(id, sku, precio_lista, fecha_vigencia, fecha_termino) {
    $("#precioId").val(id);
    $("#precioSku").val(sku);
    $("#precioLista").val(precio_lista);
    $("#precioVigencia").val(fecha_vigencia);
    $("#precioTermino").val(fecha_termino);
    $("#modalPrecioLabel").html("<i class=\"fas fa-edit me-2\"></i>Editar Precio");
    $("#btnGuardarPrecio").html("<i class=\"fas fa-save me-1\"></i>Actualizar");
    $("#precioAccion").val("editar");
    $("#precioSku").prop("readonly", true);
    new bootstrap.Modal(document.getElementById("modalPrecio")).show();
}

function eliminarPrecio(id, sku) {
    if (confirm("¿Está seguro de eliminar el precio del SKU: " + sku + "?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>';
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-dollar-sign me-2"></i>Gestión de Precios</h1>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPrecio" onclick="limpiarFormulario()">
                <i class="fas fa-plus me-2"></i>Agregar Precio
            </button>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="card">
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
                                <td colspan="7" class="text-center text-muted">No hay precios registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Precio -->
<div class="modal fade" id="modalPrecio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPrecioLabel">
                        <i class="fas fa-tag me-2"></i>Agregar Precio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="agregar" id="precioAccion">
                    <input type="hidden" name="id" id="precioId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                            <select class="form-select" name="sku" id="precioSku" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($materiales as $material): ?>
                                    <option value="<?php echo htmlspecialchars($material['sku']); ?>">
                                        <?php echo htmlspecialchars($material['sku'] . ' - ' . $material['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subcanal <span class="text-danger">*</span></label>
                            <select class="form-select" name="subcanal" id="precioSubcanal" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($subcanales as $subcanal): ?>
                                    <option value="<?php echo htmlspecialchars($subcanal); ?>">
                                        <?php echo htmlspecialchars($subcanal); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Precio Lista (CLP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="precio_lista" id="precioLista" 
                                       required min="0" step="1">
                            </div>
                            <small class="text-muted">Precio sin descuento en pesos chilenos</small>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarPrecio">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function limpiarFormulario() {
    $("#precioId").val("");
    $("#precioSku").val("");
    $("#precioLista").val("");
    $("#precioVigencia").val("");
    $("#precioTermino").val("");
    $("#precioAccion").val("agregar");
    $("#modalPrecioLabel").html('<i class="fas fa-tag me-2"></i>Agregar Precio');
    $("#btnGuardarPrecio").html('<i class="fas fa-save me-1"></i>Guardar');
    $("#precioSku").prop("readonly", false);
}
</script>