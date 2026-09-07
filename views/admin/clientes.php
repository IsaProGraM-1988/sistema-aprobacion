<?php
$titulo = 'Gestión de Clientes';
$scripts = '<script>
$(document).ready(function() {
    //SOLO INICIALIZAR SI HAY DATOS REALES
    var tbody = $("#tablaClientes tbody");
    var hasData = false;
    
    tbody.find("tr").each(function() {
        if (!$(this).find("td[colspan]").length) {
            hasData = true;
        }
    });
    
    if (hasData) {
        try {
            $("#tablaClientes").DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                order: [[1, "asc"]],
                pageLength: 25,
                dom: "<\"row\"<\"col-sm-6\"l><\"col-sm-6\"f>>rtip",
                autoWidth: false
            });
        } catch(e) {
            console.log("Error al inicializar DataTable:", e);
        }
    } else {
        if ($.fn.DataTable.isDataTable("#tablaClientes")) {
            $("#tablaClientes").DataTable().destroy();
        }
        $("#tablaClientes_wrapper").remove();
        $("#tablaClientes").after(\'<div class="alert alert-info text-center py-3"><i class="fas fa-info-circle me-2"></i>No hay clientes registrados</div>\');
    }
});

function editarCliente(id, codigo, nombre, subcanal, canal, pagador) {
    $("#clienteId").val(id);
    $("#clienteCodigo").val(codigo);
    $("#clienteNombre").val(nombre);
    $("#clienteSubcanal").val(subcanal);
    $("#clienteCanal").val(canal);
    $("#clientePagador").val(pagador);
    $("#modalClienteLabel").text("Editar Cliente");
    $("#btnGuardarCliente").html("<i class=\"fas fa-save me-1\"></i>Actualizar");
    $("#clienteAccion").val("editar");
    new bootstrap.Modal(document.getElementById("modalCliente")).show();
}

function eliminarCliente(id, nombre) {
    if (confirm("¿Está seguro de eliminar el cliente: " + nombre + "?")) {
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
        <h1><i class="fas fa-users me-2"></i>Gestión de Clientes</h1>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="limpiarFormulario()">
                <i class="fas fa-plus me-2"></i>Agregar Cliente
            </button>
            <a href="<?php echo BASE_URL; ?>public/index.php?route=admin/configuracion" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="card">
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
                                <td colspan="6" class="text-center text-muted">No hay clientes registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClienteLabel">
                        <i class="fas fa-user-plus me-2"></i>Agregar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
                    <input type="hidden" name="accion" value="agregar" id="clienteAccion">
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarCliente">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function limpiarFormulario() {
    $("#clienteId").val("");
    $("#clienteCodigo").val("");
    $("#clienteNombre").val("");
    $("#clienteSubcanal").val("");
    $("#clienteCanal").val("");
    $("#clientePagador").val("");
    $("#clienteAccion").val("agregar");
    $("#modalClienteLabel").html('<i class="fas fa-user-plus me-2"></i>Agregar Cliente');
    $("#btnGuardarCliente").html('<i class="fas fa-save me-1"></i>Guardar');
}
</script>