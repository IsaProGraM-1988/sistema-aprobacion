/**
 * Funcionalidades para Solicitudes
 */

// Variables globales 
var skusAgregados = [];
var clienteSeleccionado = null;
var idSolicitudGlobal = null;
var solicitudGuardada = false;

// ============================================================
// INICIALIZACIÓN PRINCIPAL
// ============================================================

function inicializarNuevaSolicitud() {
    console.log("🚀 Inicializando nueva solicitud...");
    
    //VERIFICAR TOKEN CSRF
    var token = $('input[name="csrf_token"]').val() || $('#csrf_token').val();
    console.log("🔑 Token CSRF encontrado:", token);
    
    if (!token || token === '') {
        console.error("❌ TOKEN CSRF NO ENCONTRADO!");
        mostrarToast('Error: Token de seguridad no encontrado. Recargue la página.', 'danger');
        return;
    }
    
    // Configurar fechas
    var hoy = new Date();
    var manana = new Date(hoy);
    manana.setDate(manana.getDate() + 1);
    var fechaMin = manana.toISOString().split('T')[0];
    $('#fechaInicio').attr('min', fechaMin);
    
    var maxFecha = new Date(manana);
    maxFecha.setFullYear(maxFecha.getFullYear() + 1);
    $('#fechaTermino').attr('max', maxFecha.toISOString().split('T')[0]);
    
    // Eventos
    $('#buscarCliente').on('click', buscarCliente);
    $('#codigoCliente').on('keypress', function(e) {
        if (e.which === 13) { e.preventDefault(); buscarCliente(); }
    });
    
    $('#buscarSku').on('click', buscarSku);
    $('#skuInput').on('keypress', function(e) {
        if (e.which === 13) { e.preventDefault(); buscarSku(); }
    });
    
    $('#precioPropuesto, #porcentajeDescuento').on('input', calcularDescuento);
    
    $('#btnAgregarSku').on('click', agregarSku);
    $('#btnVolverPaso1').on('click', function() { mostrarPaso(1); });
    $('#btnVolverPaso2').on('click', function() { mostrarPaso(2); });
    $('#btnRevisarSolicitud').on('click', revisarSolicitud);
    
    // Evento del botón enviar
    $('#btnEnviarSolicitud').on('click', function() {
        console.log("📤 Click en botón Enviar Solicitud");
        enviarSolicitud();
    });
    
    $('#btnGuardarBorrador').on('click', guardarBorrador);
    
    // EVENTO DEL CHECKBOX - DIRECTO
    $('#confirmarEnvio').on('change', function() {
        if ($(this).is(':checked')) {
            $('#btnEnviarSolicitud').prop('disabled', false);
            $('#btnEnviarSolicitud').removeClass('btn-secondary').addClass('btn-primary');
            console.log("✅ Botón de envío HABILITADO");
        } else {
            $('#btnEnviarSolicitud').prop('disabled', true);
            $('#btnEnviarSolicitud').removeClass('btn-primary').addClass('btn-secondary');
            console.log("❌ Botón de envío DESHABILITADO");
        }
    });
    
    // FORZAR ESTADO INICIAL
    $('#confirmarEnvio').prop('checked', false);
    $('#btnEnviarSolicitud').prop('disabled', true);
    
    // RESETEAR VARIABLES GLOBALES
    idSolicitudGlobal = null;
    solicitudGuardada = false;
    $('#idSolicitud').val('0');
    
    console.log("✅ Solicitud inicializada");
}

// ============================================================
// BUSCAR CLIENTE
// ============================================================

function buscarCliente() {
    var codigo = $('#codigoCliente').val().trim();
    if (!codigo) {
        mostrarToast('Ingrese un código de cliente', 'warning');
        return;
    }
    
    $.ajax({
        url: BASE_URL + 'public/index.php?route=api/clientes',
        method: 'POST',
        data: JSON.stringify({ codigo: codigo }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                clienteSeleccionado = response.data;
                $('#infoCliente').html(`
                    <div class="alert alert-success">
                        <strong>${clienteSeleccionado.nombre}</strong><br>
                        Subcanal: ${clienteSeleccionado.subcanal} | Canal: ${clienteSeleccionado.canal}
                        ${clienteSeleccionado.pagador ? ' | Pagador: ' + clienteSeleccionado.pagador : ''}
                    </div>
                `);
                mostrarToast('Cliente encontrado: ' + clienteSeleccionado.nombre, 'success');
            } else {
                clienteSeleccionado = null;
                $('#infoCliente').html(`<div class="alert alert-danger">${response.message}</div>`);
                mostrarToast(response.message, 'danger');
            }
        },
        error: function() {
            mostrarToast('Error al buscar cliente', 'danger');
        }
    });
}

// ============================================================
// BUSCAR SKU
// ============================================================

function buscarSku() {
    var sku = $('#skuInput').val().trim();
    if (!sku) {
        mostrarToast('Ingrese un SKU', 'warning');
        return;
    }
    
    if (!clienteSeleccionado) {
        mostrarToast('Primero debe seleccionar un cliente', 'warning');
        return;
    }
    
    $.ajax({
        url: BASE_URL + 'public/index.php?route=api/materiales',
        method: 'POST',
        data: JSON.stringify({ 
            sku: sku,
            subcanal: clienteSeleccionado.subcanal 
        }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                var material = response.data;
                var precio = response.precio;
                
                if (!precio) {
                    $('#infoSku').html(`<div class="alert alert-warning">⚠️ Precio de Lista no existe</div>`);
                    return;
                }
                
                $('#infoSku').html(`
                    <div class="alert alert-info">
                        <strong>${material.nombre}</strong><br>
                        Familia: ${material.familia} | Unidad: ${material.unidad_carga}<br>
                        Precio Lista: $${formatearNumero(precio.precio_lista)}
                    </div>
                `);
                $('#infoSku').data('material', material);
                $('#infoSku').data('precio', precio);
                
                $('#precioPropuesto').val(precio.precio_lista);
                calcularDescuento();
                
                mostrarToast('SKU encontrado: ' + material.nombre, 'success');
            } else {
                $('#infoSku').html(`<div class="alert alert-danger">${response.message}</div>`);
            }
        },
        error: function() {
            mostrarToast('Error al buscar SKU', 'danger');
        }
    });
}

// ============================================================
// CALCULAR DESCUENTO
// ============================================================

function calcularDescuento() {
    var precioLista = parseFloat($('#infoSku').data('precio')?.precio_lista || 0);
    var precioPropuesto = parseFloat($('#precioPropuesto').val()) || 0;
    var porcentaje = parseFloat($('#porcentajeDescuento').val()) || 0;
    
    if (precioLista > 0) {
        if (precioPropuesto > 0) {
            var descuento = ((precioLista - precioPropuesto) / precioLista) * 100;
            $('#porcentajeDescuento').val(descuento.toFixed(1));
        } else if (porcentaje > 0) {
            var precio = precioLista * (1 - porcentaje / 100);
            $('#precioPropuesto').val(precio.toFixed(0));
        }
    }
}

// ============================================================
// AGREGAR SKU
// ============================================================

function agregarSku() {
    var sku = $('#skuInput').val().trim();
    var material = $('#infoSku').data('material');
    var precio = $('#infoSku').data('precio');
    var precioPropuesto = parseFloat($('#precioPropuesto').val());
    var porcentaje = parseFloat($('#porcentajeDescuento').val());
    var volumen = parseFloat($('#volumenEsperado').val());
    
    if (!clienteSeleccionado) {
        mostrarToast('Seleccione un cliente primero', 'warning');
        return;
    }
    if (!material) {
        mostrarToast('Busque un SKU válido primero', 'warning');
        return;
    }
    if (!precio) {
        mostrarToast('Este SKU no tiene precio de lista', 'warning');
        return;
    }
    if (!precioPropuesto || precioPropuesto <= 0) {
        mostrarToast('Ingrese un precio propuesto válido', 'warning');
        return;
    }
    if (precioPropuesto > precio.precio_lista) {
        mostrarToast('El precio propuesto no puede ser mayor al precio de lista', 'warning');
        return;
    }
    if (!volumen || volumen <= 0) {
        mostrarToast('Ingrese un volumen esperado válido', 'warning');
        return;
    }
    if (skusAgregados.length >= 30) {
        mostrarToast('Máximo 30 SKUs', 'warning');
        return;
    }
    if (skusAgregados.find(function(s) { return s.sku === sku; })) {
        mostrarToast('Este SKU ya fue agregado', 'warning');
        return;
    }
    
    var nuevoSku = {
        sku: sku,
        nombre: material.nombre,
        familia: material.familia,
        unidad: material.unidad_carga,
        precio_lista: parseFloat(precio.precio_lista),
        precio_propuesto: precioPropuesto,
        porcentaje_descuento: parseFloat(porcentaje) || 0,
        volumen_esperado: volumen
    };
    
    skusAgregados.push(nuevoSku);
    actualizarTablaSkus();
    
    // Limpiar campos
    $('#skuInput').val('');
    $('#infoSku').html('').data('material', null).data('precio', null);
    $('#precioPropuesto').val('');
    $('#porcentajeDescuento').val('');
    $('#volumenEsperado').val('');
    
    mostrarToast('SKU agregado correctamente', 'success');
}

// ============================================================
// ACTUALIZAR TABLA SKUS
// ============================================================

function actualizarTablaSkus() {
    var tbody = $('#cuerpoTablaSkus');
    tbody.empty();
    
    if (skusAgregados.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted">No hay SKUs agregados</td></tr>');
        $('#contadorSkus').text('0/30');
        return;
    }
    
    skusAgregados.forEach(function(sku, index) {
        var descuento = parseFloat(sku.porcentaje_descuento) || 0;
        var row = `
            <tr>
                <td><strong>${sku.sku}</strong></td>
                <td>${sku.nombre}</td>
                <td>${sku.familia}</td>
                <td>$${formatearNumero(sku.precio_lista)}</td>
                <td>$${formatearNumero(sku.precio_propuesto)}</td>
                <td><span class="badge bg-${descuento > 10 ? 'warning' : 'info'}">${descuento.toFixed(1)}%</span></td>
                <td>${formatearNumero(sku.volumen_esperado)}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="eliminarSku(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    $('#contadorSkus').text(skusAgregados.length + '/30');
    mostrarPaso(2);
}

function eliminarSku(index) {
    if (confirm('¿Eliminar este SKU?')) {
        skusAgregados.splice(index, 1);
        actualizarTablaSkus();
        mostrarToast('SKU eliminado', 'info');
    }
}

// ============================================================
// MOSTRAR PASO
// ============================================================

function mostrarPaso(paso) {
    $('#paso1, #paso2, #paso3').hide();
    
    switch(paso) {
        case 1:
            $('#paso1').show();
            $('#progresoPaso').css('width', '33%').text('Paso 1: Datos Generales');
            break;
        case 2:
            $('#paso2').show();
            $('#progresoPaso').css('width', '66%').text('Paso 2: Agregar SKUs');
            break;
        case 3:
            $('#paso3').show();
            $('#progresoPaso').css('width', '100%').text('Paso 3: Revisión y Envío');
            $('#confirmarEnvioContainer').show();
            $('#confirmarEnvio').prop('checked', false);
            $('#btnEnviarSolicitud').prop('disabled', true);
            console.log("✅ Paso 3 mostrado");
            break;
    }
}

// ============================================================
// REVISAR SOLICITUD - CON GUARDADO AUTOMÁTICO
// ============================================================

function revisarSolicitud() {
    console.log("📋 Iniciando revisión de solicitud...");
    
    if (!clienteSeleccionado) {
        mostrarToast('Seleccione un cliente primero', 'warning');
        return;
    }
    
    if (skusAgregados.length === 0) {
        mostrarToast('Agregue al menos un SKU', 'warning');
        return;
    }
    
    if (!validarFechas()) {
        return;
    }
    
    var idActual = $('#idSolicitud').val();
    console.log("🔍 ID actual antes de guardar:", idActual);
    
    if (!idActual || idActual === '0' || idActual === '') {
        console.log("🔄 Guardando borrador automáticamente antes de revisar...");
        
        guardarBorradorAuto(function(success, id, nuevoToken) {
            if (success && id) {
                idSolicitudGlobal = id;
                $('#idSolicitud').val(id);
                // Actualizar token si viene
                if (nuevoToken) {
                    $('#csrf_token').val(nuevoToken);
                    $('input[name="csrf_token"]').val(nuevoToken);
                }
                console.log("✅ Borrador guardado automáticamente. ID:", id);
                mostrarPasoConResumen();
            } else {
                mostrarToast('Error al guardar el borrador automático. Intente manualmente.', 'danger');
            }
        });
    } else {
        idSolicitudGlobal = idActual;
        mostrarPasoConResumen();
    }
}

// ============================================================
// GUARDAR BORRADOR AUTOMÁTICO
// ============================================================

function guardarBorradorAuto(callback) {
    console.log("📤 Guardando borrador automático...");
    
    if (!clienteSeleccionado) {
        if (callback) callback(false, null);
        return;
    }
    if (skusAgregados.length === 0) {
        if (callback) callback(false, null);
        return;
    }
    
    var csrfToken = $('input[name="csrf_token"]').val() || $('#csrf_token').val();
    
    var datos = {
        codigo_cliente: clienteSeleccionado.codigo,
        skus: JSON.stringify(skusAgregados.map(function(s) {
            return {
                sku: s.sku,
                precio_propuesto: s.precio_propuesto,
                volumen_esperado: s.volumen_esperado
            };
        })),
        fecha_inicio: $('#fechaInicio').val(),
        fecha_termino: $('#fechaTermino').val(),
        motivo: $('#motivo').val(),
        csrf_token: csrfToken
    };
    
    console.log("📤 Datos borrador automático:", datos);
    
    $.ajax({
        url: BASE_URL + 'public/index.php?route=solicitudes/guardar-borrador',
        method: 'POST',
        data: JSON.stringify(datos),
        contentType: 'application/json',
        success: function(response) {
            console.log("📥 Respuesta guardar auto:", response);
            if (response.success) {
                idSolicitudGlobal = response.id_solicitud;
                solicitudGuardada = true;
                $('#idSolicitud').val(response.id_solicitud);
                
                //ACTUALIZAR TOKEN CSRF
                if (response.csrf_token) {
                    $('#csrf_token').val(response.csrf_token);
                    $('input[name="csrf_token"]').val(response.csrf_token);
                    console.log('✅ Token CSRF actualizado automáticamente');
                }
                
                if (callback) callback(true, response.id_solicitud, response.csrf_token);
            } else {
                console.error("❌ Error al guardar auto:", response.message);
                if (callback) callback(false, null);
            }
        },
        error: function(xhr) {
            console.error("❌ Error AJAX guardar auto:", xhr);
            if (callback) callback(false, null);
        }
    });
}

// ============================================================
// MOSTRAR PASO CON RESUMEN
// ============================================================

function mostrarPasoConResumen() {
    console.log("📊 Generando resumen con ID:", $('#idSolicitud').val());
    
    var html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>SKU</th><th>Material</th><th>Precio Lista</th><th>Precio Propuesto</th><th>% Desc.</th><th>Volumen</th></tr></thead>
                <tbody>
    `;
    
    skusAgregados.forEach(function(sku) {
        html += `
            <tr>
                <td><strong>${sku.sku}</strong></td>
                <td>${sku.nombre}</td>
                <td>$${formatearNumero(sku.precio_lista)}</td>
                <td>$${formatearNumero(sku.precio_propuesto)}</td>
                <td>${(parseFloat(sku.porcentaje_descuento) || 0).toFixed(1)}%</td>
                <td>${formatearNumero(sku.volumen_esperado)}</td>
            </tr>
        `;
    });
    
    html += `</tbody></table></div>`;
    $('#resumenSolicitud').html(html);
    
    var total = 0;
    skusAgregados.forEach(function(sku) {
        total += parseFloat(sku.porcentaje_descuento) || 0;
    });
    var promedio = total / skusAgregados.length;
    $('#descuentoPromedio').text(promedio.toFixed(1) + '%');
    
    mostrarPaso(3);
    
    $('#btnRevisarSolicitud').hide();
    $('#btnGuardarBorrador').show();
    $('#btnEnviarSolicitud').show();
    
    console.log("✅ Revisión completada. ID en campo:", $('#idSolicitud').val());
    console.log("✅ ID Global:", idSolicitudGlobal);
}

// ============================================================
// GUARDAR BORRADOR MANUAL
// ============================================================

function guardarBorrador() {
    if (!clienteSeleccionado) {
        mostrarToast('Seleccione un cliente', 'warning');
        return;
    }
    if (skusAgregados.length === 0) {
        mostrarToast('Agregue al menos un SKU', 'warning');
        return;
    }
    if (!validarFechas()) {
        return;
    }
    
    var csrfToken = $('input[name="csrf_token"]').val() || $('#csrf_token').val();
    
    var datos = {
        codigo_cliente: clienteSeleccionado.codigo,
        skus: JSON.stringify(skusAgregados.map(function(s) {
            return {
                sku: s.sku,
                precio_propuesto: s.precio_propuesto,
                volumen_esperado: s.volumen_esperado
            };
        })),
        fecha_inicio: $('#fechaInicio').val(),
        fecha_termino: $('#fechaTermino').val(),
        motivo: $('#motivo').val(),
        csrf_token: csrfToken
    };
    
    console.log("📤 Guardando borrador con token:", datos.csrf_token);
    
    $('#btnGuardarBorrador').prop('disabled', true).html('Guardando...');
    
    $.ajax({
        url: BASE_URL + 'public/index.php?route=solicitudes/guardar-borrador',
        method: 'POST',
        data: JSON.stringify(datos),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                mostrarToast('Borrador guardado', 'success');
                idSolicitudGlobal = response.id_solicitud;
                solicitudGuardada = true;
                $('#idSolicitud').val(response.id_solicitud);
                
                // 🔥 ACTUALIZAR TOKEN CSRF
                if (response.csrf_token) {
                    $('#csrf_token').val(response.csrf_token);
                    $('input[name="csrf_token"]').val(response.csrf_token);
                    console.log('✅ Token CSRF actualizado manualmente');
                }
                
                console.log('✅ ID Solicitud guardado:', response.id_solicitud);
            } else {
                mostrarToast(response.message, 'danger');
            }
            $('#btnGuardarBorrador').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Borrador');
        },
        error: function() {
            mostrarToast('Error al guardar', 'danger');
            $('#btnGuardarBorrador').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Borrador');
        }
    });
}

// ============================================================
// ENVIAR SOLICITUD
// ============================================================

function enviarSolicitud() {
    console.log("📤 ENVIANDO SOLICITUD...");
    
    // 1. Verificar checkbox
    if (!$('#confirmarEnvio').is(':checked')) {
        mostrarToast('Debe confirmar el envío marcando el checkbox', 'warning');
        return;
    }
    
    // 2. Verificar SKUs
    if (skusAgregados.length === 0) {
        mostrarToast('Agregue al menos un SKU', 'warning');
        return;
    }
    
    // 3. OBTENER ID - PRIORIDAD: variable global > campo oculto
    var idSolicitud = idSolicitudGlobal || $('#idSolicitud').val();
    
    if ($('#idSolicitud').val() && $('#idSolicitud').val() !== '0' && !idSolicitudGlobal) {
        idSolicitud = $('#idSolicitud').val();
        idSolicitudGlobal = idSolicitud;
        console.log("🔄 Actualizando global desde campo:", idSolicitud);
    }
    
    console.log("📦 ID Solicitud a enviar:", idSolicitud);
    
    if (!idSolicitud || idSolicitud === '0' || idSolicitud === '' || idSolicitud === 'null') {
        mostrarToast('❌ Error: No se encontró el ID de la solicitud. Guarde como borrador primero.', 'danger');
        return;
    }
    
    // 4. Obtener token CSRF actualizado
    var csrfToken = $('input[name="csrf_token"]').val() || $('#csrf_token').val();
    console.log("🔑 Token CSRF obtenido:", csrfToken);
    
    if (!csrfToken || csrfToken === '') {
        mostrarToast('Error: Token de seguridad no encontrado. Recargue la página.', 'danger');
        return;
    }
    
    // 5. Preparar datos
    var datos = {
        id_solicitud: parseInt(idSolicitud),
        csrf_token: csrfToken
    };
    
    console.log("📤 Datos a enviar:", datos);
    
    // 6. Deshabilitar botón
    $('#btnEnviarSolicitud').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Enviando...');
    
    // 7. Enviar AJAX
    $.ajax({
        url: BASE_URL + 'public/index.php?route=solicitudes/enviar',
        method: 'POST',
        data: JSON.stringify(datos),
        contentType: 'application/json',
        success: function(response) {
            console.log("📥 Respuesta:", response);
            
            if (response.success) {
                mostrarToast('✅ ' + response.message, 'success');
                // Actualizar token si viene
                if (response.csrf_token) {
                    $('#csrf_token').val(response.csrf_token);
                    $('input[name="csrf_token"]').val(response.csrf_token);
                }
                setTimeout(function() {
                    window.location.href = BASE_URL + 'public/index.php?route=solicitudes/mis-solicitudes';
                }, 1500);
            } else {
                mostrarToast('❌ ' + response.message, 'danger');
                $('#btnEnviarSolicitud').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Enviar Solicitud');
                $('#confirmarEnvio').prop('checked', false);
                if (response.message && response.message.includes('Token')) {
                    console.log("🔄 Token inválido, recargando...");
                    // Recargar token de la página
                    location.reload();
                }
            }
        },
        error: function(xhr) {
            console.log("❌ Error AJAX:", xhr.responseText);
            try {
                var errorResponse = JSON.parse(xhr.responseText);
                mostrarToast('❌ ' + (errorResponse.message || 'Error al enviar'), 'danger');
            } catch(e) {
                mostrarToast('Error al enviar: ' + xhr.status, 'danger');
            }
            $('#btnEnviarSolicitud').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Enviar Solicitud');
            $('#confirmarEnvio').prop('checked', false);
        }
    });
}

// ============================================================
// VALIDAR FECHAS
// ============================================================

function validarFechas() {
    var fechaInicio = $('#fechaInicio').val();
    var fechaTermino = $('#fechaTermino').val();
    
    if (!fechaInicio || !fechaTermino) {
        mostrarToast('Ingrese fechas de inicio y término', 'warning');
        return false;
    }
    
    var inicio = new Date(fechaInicio);
    var termino = new Date(fechaTermino);
    var hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (inicio < hoy) {
        mostrarToast('La fecha de inicio no puede ser anterior a hoy', 'warning');
        return false;
    }
    if (termino < inicio) {
        mostrarToast('La fecha de término debe ser posterior a la fecha de inicio', 'warning');
        return false;
    }
    
    var diffDays = Math.ceil(Math.abs(termino - inicio) / (1000 * 60 * 60 * 24));
    if (diffDays > 365) {
        mostrarToast('La promoción no puede superar 1 año', 'warning');
        return false;
    }
    
    return true;
}

// ============================================================
// UTILIDADES
// ============================================================

function formatearNumero(numero) {
    if (!numero) return '0';
    return Math.round(numero).toLocaleString('es-CL');
}

function mostrarToast(mensaje, tipo) {
    if (typeof window.mostrarToast === 'function') {
        window.mostrarToast(mensaje, tipo);
    } else {
        alert(mensaje);
    }
}

// ============================================================
// EXPONER FUNCIONES GLOBALES
// ============================================================

window.skusAgregados = skusAgregados;
window.clienteSeleccionado = clienteSeleccionado;
window.idSolicitudGlobal = idSolicitudGlobal;
window.solicitudGuardada = solicitudGuardada;
window.agregarSku = agregarSku;
window.actualizarTablaSkus = actualizarTablaSkus;
window.eliminarSku = eliminarSku;
window.revisarSolicitud = revisarSolicitud;
window.guardarBorrador = guardarBorrador;
window.guardarBorradorAuto = guardarBorradorAuto;
window.enviarSolicitud = enviarSolicitud;
window.mostrarPaso = mostrarPaso;
window.mostrarPasoConResumen = mostrarPasoConResumen;
window.buscarCliente = buscarCliente;
window.buscarSku = buscarSku;
window.calcularDescuento = calcularDescuento;
window.validarFechas = validarFechas;
window.formatearNumero = formatearNumero;
window.inicializarNuevaSolicitud = inicializarNuevaSolicitud;

console.log("✅ solicitudes.js cargado correctamente (VERSIÓN CORREGIDA)");