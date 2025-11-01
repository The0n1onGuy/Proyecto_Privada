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
    console.log("Inicializando vista de residentes..."); // Para depuración

    const tablaResidentes = $('#tablaResidentes');

    // Verifica si la tabla existe en el DOM antes de intentar inicializarla
    if(tablaResidentes.length === 0) {
        console.warn("Tabla #tablaResidentes no encontrada en el DOM.");
        return;
    }

    // Destruye cualquier instancia previa de DataTable en esta tabla
    // Usar "destroy": true en las opciones también ayuda, pero hacerlo explícito aquí es más seguro.
    if ($.fn.DataTable.isDataTable(tablaResidentes)) {
        console.log("Destruyendo DataTable existente..."); // Para depuración
        tablaResidentes.DataTable().destroy();
        // A veces es útil limpiar el tbody después de destruir para evitar duplicados visuales momentáneos
        // tablaResidentes.find('tbody').empty();
    }

    // Inicializa DataTable
    console.log("Inicializando DataTable..."); // Para depuración
    tablaResidentes.DataTable({
        "language": {
            "url": "/js/Utilities/spanish.json" // Asegúrate que este archivo exista y sea accesible
        },
        "order": [
            [5, "asc"], // Ordenar por número de casa (columna índice 5)
            [2, "desc"] // Luego por Tipo (Propietario primero) (columna índice 2)
        ],
        // Opciones adicionales que pueden ayudar:
        "retrieve": true, // Intenta reutilizar la instancia si ya existe (aunque destroy debería manejarlo)
        "paging": true, // Asegúrate de que la paginación esté como la deseas (true por defecto)
        "searching": true, // Asegúrate de que la búsqueda esté como la deseas (true por defecto)
        "info": true // Asegúrate de que la información esté como la deseas (true por defecto)
        // Considera añadir 'destroy: true' si sigues teniendo problemas, aunque ya lo hacemos manualmente.
        // "destroy": true
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