/**
 * Sistema de Aprobaciones Quillayes Surlat
 * JavaScript Principal
 */

$(document).ready(function() {
    // Toggle sidebar en móvil
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('shifted');
    });

    // Auto cerrar alertas después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Confirmar acciones con modal
    $('.btn-confirmar').on('click', function(e) {
        if (!confirm('¿Estás seguro de realizar esta acción?')) {
            e.preventDefault();
            return false;
        }
    });

    // Formatear números en inputs
    $('.formato-numero').on('input', function() {
        let valor = this.value.replace(/\./g, '').replace(/,/g, '.');
        if (!isNaN(valor) && valor !== '') {
            let partes = valor.split('.');
            partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            this.value = partes.join(',');
        }
    });

    // Validar fechas
    $('input[type="date"]').on('change', function() {
        let fecha = new Date(this.value);
        let hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if (fecha < hoy) {
            alert('La fecha no puede ser anterior a hoy');
            this.value = '';
        }
    });

    // Función para mostrar toast
    window.mostrarToast = function(mensaje, tipo = 'success') {
        const colores = {
            success: 'bg-success',
            danger: 'bg-danger',
            warning: 'bg-warning',
            info: 'bg-info'
        };
        
        const iconos = {
            success: 'fa-check-circle',
            danger: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        let toast = `
            <div class="toast align-items-center text-white ${colores[tipo]} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${iconos[tipo]} me-2"></i>${mensaje}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        let container = $('.toast-container');
        if (container.length === 0) {
            container = $('<div class="toast-container"></div>');
            $('body').append(container);
        }
        
        let $toast = $(toast);
        container.append($toast);
        $toast.toast({ delay: 5000 });
        $toast.toast('show');
        
        setTimeout(function() {
            $toast.remove();
        }, 5000);
    };

    // Función para cargar datos con AJAX
    window.cargarDatos = function(url, datos, metodo = 'POST') {
        // Asegurar que la URL use el formato correcto
        if (!url.includes('public/index.php')) {
            url = BASE_URL + 'public/index.php?route=' + url;
        }
        
        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: metodo,
                data: JSON.stringify(datos),
                contentType: 'application/json',
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    };
});