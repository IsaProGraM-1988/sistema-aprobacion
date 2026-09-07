/**
 * Validaciones del sistema
 */

// Validación de formularios
$(document).ready(function() {
    // Validación de email corporativo
    $('input[type="email"]').on('blur', function() {
        let email = $(this).val();
        if (email && !email.includes('@quillayessurlat.cl') && !email.includes('@admin')) {
            $(this).addClass('is-invalid');
            $(this).after('<div class="invalid-feedback">Debe usar un correo corporativo @quillayessurlat.cl</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Validación de número con formato chileno
    $('.input-numero-chileno').on('input', function() {
        let valor = $(this).val();
        valor = valor.replace(/\./g, '').replace(/,/g, '.');
        if (!isNaN(valor) && valor !== '') {
            let partes = valor.split('.');
            partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            $(this).val(partes.join(','));
        }
    });

    // Validación de RUT (si es necesario)
    $('.input-rut').on('input', function() {
        let rut = $(this).val();
        rut = rut.replace(/\./g, '').replace(/-/g, '');
        if (rut.length > 8) {
            let dv = rut.charAt(rut.length - 1);
            let numero = rut.substring(0, rut.length - 1);
            if (validarRut(numero, dv)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        }
    });
});

function validarRut(rut, dv) {
    let suma = 0;
    let multiplicador = 2;
    
    for (let i = rut.length - 1; i >= 0; i--) {
        suma += parseInt(rut.charAt(i)) * multiplicador;
        multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
    }
    
    let dvEsperado = 11 - (suma % 11);
    dvEsperado = dvEsperado === 11 ? 0 : dvEsperado === 10 ? 'K' : dvEsperado.toString();
    
    return dvEsperado === dv.toUpperCase();
}

// Validación de formularios con Bootstrap
(function() {
    'use strict';
    
    let forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Funciones de validación general
window.validarEmail = function(email) {
    let re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

window.validarEmailCorporativo = function(email) {
    return email.includes('@quillayessurlat.cl');
};

window.validarNumero = function(numero) {
    return !isNaN(numero) && numero > 0;
};

window.validarFecha = function(fecha) {
    let fechaObj = new Date(fecha);
    return !isNaN(fechaObj.getTime());
};

window.validarRangoFechas = function(fechaInicio, fechaTermino) {
    let inicio = new Date(fechaInicio);
    let termino = new Date(fechaTermino);
    let hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (inicio < hoy) {
        return 'La fecha de inicio no puede ser anterior a hoy';
    }
    
    if (termino < inicio) {
        return 'La fecha de término debe ser posterior a la fecha de inicio';
    }
    
    let diffTime = Math.abs(termino - inicio);
    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays > 365) {
        return 'La promoción no puede superar 1 año';
    }
    
    return null;
};

window.validarDescuento = function(precioLista, precioPropuesto) {
    if (precioPropuesto > precioLista) {
        return 'El precio propuesto no puede ser mayor al precio de lista';
    }
    
    let descuento = ((precioLista - precioPropuesto) / precioLista) * 100;
    if (descuento < 0) {
        return 'El descuento no puede ser negativo';
    }
    
    if (descuento > 50) {
        return 'El descuento no puede superar el 50%';
    }
    
    return null;
};

window.validarSKU = function(sku) {
    // Validar formato de SKU (letras y números, mínimo 4 caracteres)
    let re = /^[A-Za-z0-9]{4,20}$/;
    return re.test(sku);
};

// Mostrar errores de validación
window.mostrarErrorValidacion = function(input, mensaje) {
    input.addClass('is-invalid');
    let feedback = input.next('.invalid-feedback');
    if (feedback.length === 0) {
        feedback = $('<div class="invalid-feedback"></div>');
        input.after(feedback);
    }
    feedback.text(mensaje);
};

window.limpiarErrorValidacion = function(input) {
    input.removeClass('is-invalid');
    input.next('.invalid-feedback').remove();
};