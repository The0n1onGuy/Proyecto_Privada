// public/js/Resident/residents.js

// --- MANEJADOR PARA EL CAMBIO DE FILTRO (Registrado una sola vez globalmente) ---
// Usamos $(document).off().on() para asegurarnos de que solo haya un listener activo
// El namespace '.residentsFilter' ayuda a identificar este listener específico.
$(document).off('change.residentsFilter').on('change.residentsFilter', '#residentFilter', function() {
    var filterValue = $(this).val();
    if (window.loadSection) {
        // Usamos window.location.hash para mantener la URL base consistente
        // y solo añadir/modificar el parámetro de filtro.
        window.loadSection('residents', '?filter=' + filterValue);
    } else {
        console.error("La función loadSection no está definida.");
    }
});

// --- FUNCIÓN GLOBAL DE INICIALIZACIÓN PARA ESTA VISTA ---
// Esta función será llamada por resident_panel.js después de cargar el contenido HTML y este script.
function initializeResidentsView() {
    console.log("Inicializando vista de residentes...");

    const tablaResidentes = $('#tablaResidentes');

    if(tablaResidentes.length === 0) {
        console.warn("Tabla #tablaResidentes no encontrada.");
        return;
    }

    if ($.fn.DataTable.isDataTable(tablaResidentes)) {
        tablaResidentes.DataTable().destroy();
    }

    tablaResidentes.DataTable({
        "language": {
            "url": "/js/Utilities/spanish.json"
        },
        "responsive": true,
        
        // --- AQUÍ ESTÁ EL TRUCO DEL DOM ---
        // Explicación de las letras:
        // 'top-wrapper' = clase contenedora superior
        // 'my-custom-filter' = clase donde inyectaremos tu select
        // 'f' = filter (input de búsqueda)
        // 'rt' = processing (r) y table (t)
        // 'p' = pagination (paginación)
        // Nota: Hemos eliminado la 'l' (length) intencionalmente.
        "dom": '<"top-wrapper" <"my-custom-filter"> f > rt <"bottom" p >',

        "order": [
            [5, "asc"], 
            [2, "desc"]
        ],
        
        // --- FUNCIÓN QUE SE EJECUTA AL TERMINAR DE CREAR LA TABLA ---
        "initComplete": function(settings, json) {
            // 1. Seleccionamos tu contenedor de filtro original
            var $originalFilter = $('#customFilterDestination');
            
            // 2. Seleccionamos el destino dentro de la tabla (el hueco que creamos en 'dom')
            var $destination = $('.my-custom-filter');
            
            // 3. Movemos el filtro dentro de la estructura de DataTables
            $originalFilter.appendTo($destination);
            
            // 4. (Opcional) Ajustamos estilos visuales para que se vea bonito junto al buscador
            $originalFilter.css({
                'display': 'flex',
                'align-items': 'center',
                'margin-right': '20px' // Espacio entre el filtro y otros elementos si los hubiera
            });
        }
    });
}

// --- INICIALIZACIÓN INICIAL (Opcional, pero puede ser útil) ---
// Si esta vista se carga directamente (sin AJAX), intenta inicializarla.
// La llamada principal vendrá de `window.initializeView('residents')` en resident_panel.js
$(document).ready(function() {
    if ($('#tablaResidentes').length && !$.fn.DataTable.isDataTable('#tablaResidentes')) {
        // Solo inicializa si la tabla está presente Y no ha sido ya inicializada
        // (esto previene una doble inicialización si la página carga directamente esta sección)
        // initializeResidentsView();
        // Es mejor dejar que la lógica centralizada en resident_panel.js maneje la llamada inicial.
    }
});