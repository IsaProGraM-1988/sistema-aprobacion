<?php
$titulo = 'Nueva Solicitud de Descuento';
$scripts = '
<script>
$(document).ready(function() {
    console.log("🚀 Iniciando página nueva solicitud...");
    
    //VERIFICAR TOKEN CSRF
    var token = $("input[name=\'csrf_token\']").val();
    console.log("🔑 Token CSRF en el formulario:", token);
    
    if (!token || token === "") {
        console.error("❌ TOKEN CSRF NO ENCONTRADO!");
        mostrarToast("Error: Token de seguridad no encontrado. Recargue la página.", "danger");
        return;
    } else {
        console.log("✅ Token CSRF encontrado");
    }
    
    if (typeof inicializarNuevaSolicitud === "function") {
        inicializarNuevaSolicitud();
    } else {
        console.error("❌ solicitudes.js no cargado");
    }
    
    // Ocultar checkbox al inicio (solo visible en paso 3)
    $("#confirmarEnvioContainer").hide();
    
    //FORZAR QUE EL CHECKBOX SIEMPRE ESTÉ DESMARCADO AL INICIO
    $("#confirmarEnvio").prop("checked", false);
    $("#btnEnviarSolicitud").prop("disabled", true);
    
    // EVENTO DEL CHECKBOX - DIRECTO Y SIMPLE
    $("#confirmarEnvio").on("change", function() {
        if ($(this).is(":checked")) {
            $("#btnEnviarSolicitud").prop("disabled", false);
            $("#btnEnviarSolicitud").removeClass("btn-secondary").addClass("btn-primary");
            console.log("✅ Botón de envío HABILITADO");
        } else {
            $("#btnEnviarSolicitud").prop("disabled", true);
            $("#btnEnviarSolicitud").removeClass("btn-primary").addClass("btn-secondary");
            console.log("❌ Botón de envío DESHABILITADO");
        }
    });
    
    console.log("✅ Página nueva solicitud lista");
});
</script>
';
?>
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-plus-circle me-2"></i>Nueva Solicitud de Descuento</h1>
        <div>
            <span class="badge bg-secondary me-2">Máximo 30 SKU</span>
            <span class="badge bg-info">Tipo: Solicitud</span>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="progress" style="height: 30px;">
                <div id="progresoPaso" class="progress-bar bg-primary" role="progressbar" style="width: 33%;">
                    <span class="fw-bold">Paso 1: Datos Generales</span>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <span>1. Datos Generales</span>
                <span>2. Agregar SKU</span>
                <span>3. Revisión y Envío</span>
            </div>
        </div>
    </div>

    <form id="formSolicitud" novalidate>
        <!--TOKEN CSRF - CON name Y id para compatibilidad -->
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo Session::generarTokenCSRF(); ?>">
        <input type="hidden" name="id_solicitud" id="idSolicitud" value="0">
        
        <!-- PASO 1 -->
        <div id="paso1" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Paso 1: Datos Generales</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Código Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="codigoCliente" placeholder="Ingrese código de cliente" required>
                            <button class="btn btn-primary" type="button" id="buscarCliente"><i class="fas fa-search"></i></button>
                        </div>
                        <div id="infoCliente" class="mt-2"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="skuInput" placeholder="Ingrese código SKU" required>
                            <button class="btn btn-primary" type="button" id="buscarSku"><i class="fas fa-search"></i></button>
                        </div>
                        <div id="infoSku" class="mt-2"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Precio Propuesto (CLP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="precioPropuesto" placeholder="Precio propuesto" step="0.01" required>
                            <span class="input-group-text">CLP</span>
                        </div>
                        <small class="text-muted">O ingrese % de descuento: 
                            <input type="number" id="porcentajeDescuento" style="width: 70px; display: inline-block;" placeholder="%" step="0.1">
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Volumen de venta esperado <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="volumenEsperado" placeholder="Volumen esperado" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de inicio promocional <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fechaInicio" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de término promocional <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fechaTermino" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motivo (Opcional)</label>
                        <textarea class="form-control" id="motivo" rows="2" placeholder="Ingrese motivo o comentarios"></textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" id="btnAgregarSku">
                    <i class="fas fa-plus me-2"></i>Agregar SKU
                </button>
            </div>
        </div>

        <!-- PASO 2 -->
        <div id="paso2" class="card mb-4" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Paso 2: SKU\'s Agregados</h5>
                <span class="badge bg-secondary" id="contadorSkus">0/30</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaSkus">
                        <thead>
                            <tr>
                                <th>SKU</th><th>Material</th><th>Familia</th><th>Precio Lista</th>
                                <th>Precio Propuesto</th><th>% Desc.</th><th>Volumen</th><th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaSkus"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-secondary" id="btnVolverPaso1"><i class="fas fa-arrow-left me-2"></i>Agregar más SKU</button>
                <button type="button" class="btn btn-success float-end" id="btnRevisarSolicitud"><i class="fas fa-check me-2"></i>Revisar Solicitud</button>
            </div>
        </div>

        <!-- PASO 3 -->
        <div id="paso3" class="card mb-4" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Paso 3: Revisión y Envío</h5>
            </div>
            <div class="card-body">
                <div id="resumenSolicitud"></div>
                
                <div class="alert alert-warning" id="alertaConfirmacion">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Confirmación</h5>
                    <p>Al enviar esta solicitud, se aplicará <strong id="descuentoPromedio">0%</strong> de descuento promedio.</p>
                    <p>La solicitud será enviada a los aprobadores correspondientes según los tramos definidos.</p>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-secondary" id="btnVolverPaso2"><i class="fas fa-arrow-left me-2"></i>Volver</button>
                <button type="button" class="btn btn-primary float-end" id="btnEnviarSolicitud" disabled>
                    <i class="fas fa-paper-plane me-2"></i>Enviar Solicitud
                </button>
                <button type="button" class="btn btn-outline-secondary float-end me-2" id="btnGuardarBorrador">
                    <i class="fas fa-save me-2"></i>Guardar Borrador
                </button>
            </div>
        </div>
    </form>
</div>

<!--CHECKBOX DE CONFIRMACIÓN - SOLO VISIBLE EN PASO 3 -->
<div id="confirmarEnvioContainer" class="card mb-4" style="display: none; border: 3px solid #ffc107; background: #fff8e1;">
    <div class="card-body">
        <div class="form-check" style="padding: 10px;">
            <input class="form-check-input" type="checkbox" id="confirmarEnvio" style="width: 25px; height: 25px; cursor: pointer;">
            <label class="form-check-label ms-3" for="confirmarEnvio" style="font-size: 1.2rem; font-weight: 600; color: #856404; cursor: pointer;">
                <i class="fas fa-check-circle me-2 text-success"></i>
                Confirmo que los datos ingresados son correctos y estoy seguro de enviar esta solicitud.
            </label>
        </div>
    </div>
</div>