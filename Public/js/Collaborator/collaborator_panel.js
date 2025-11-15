// public/js/Resident/resident_panel.js
document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENTOS DEL DOM ---
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const topbar = document.getElementById('topbar');
    const contentWrap = document.getElementById('content-wrap');
    const logoutButton = document.getElementById('logout-button');
    const logoutPopup = document.getElementById('logoutPopup');

    // --- MANEJADORES DE EVENTOS PRINCIPALES ---

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            topbar.classList.toggle('main-collapsed');
            contentWrap.classList.toggle('main-collapsed');
        });
    }

    if (logoutButton && logoutPopup) {
        logoutButton.addEventListener('click', (event) => {
            event.preventDefault();
            logoutPopup.classList.add('visible');
            setTimeout(() => {
                window.location.href = logoutButton.href;
            }, 1500);
        });
    }

    // --- LÓGICA DE NAVEGACIÓN Y CARGA DE SECCIONES ---

    // Navegación por AJAX al hacer clic en los enlaces del menú
    $(document).on('click', '.nav-link', function(e) {
        const href = $(this).attr('href');
        if (href === '/logout') return;

        e.preventDefault();
        const view = $(this).data('view');
        if (view) {
            // Actualiza el hash en la URL para guardar el estado
            window.location.hash = view;
        }
    });

    // Escucha los cambios en el hash de la URL
    $(window).on('hashchange', function() {
        const view = window.location.hash.substring(1);
        if (view) {
            loadSection(view);
        }
    });

    // Función global para cargar secciones
    window.loadSection = function(view, params = '') {
        // Marca el enlace activo en el menú lateral
        $('.nav-link').removeClass('active');
        $(`.nav-link[data-view="${view}"]`).addClass('active');

        const url = `/collaborator/content/${view}${params}`;
        $('#content-wrap').fadeTo('fast', 0.3);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#content-wrap').html(response.html);
                loadAssets(response.assets, function() {
                    // --- CAMBIO AQUÍ ---
                    // Llama a la función global de inicialización específica de la vista
                    if (window.initializeView && typeof window.initializeView === 'function') {
                        window.initializeView(view);
                    }
                    // --- FIN DEL CAMBIO ---
                    $('#content-wrap').fadeTo('fast', 1);
                });
            },
            error: function(xhr, textStatus, errorThrown) {
                    if (xhr.status === 401) {

                    // 401 Unauthorized
                    showGlobalPopup("Sesión expirada - Redirigiendo al inicio de sesión...");
                    setTimeout(function() {
                        window.location.href = '/logout';
                    }, 2000);
                    
                    // Verificamos que la función del PopupHelper exista
                    if (typeof showGlobalPopup === 'function') { 
                        showGlobalPopup(
                            'Sesión Expirada', // Título
                            'Tu sesión ha caducado. Serás redirigido al inicio.', // Mensaje
                            'Entendido', // Texto del botón
                            'error'      // Tipo (para el color)
                        );
                    } else {
                        // Fallback si popup.js no se cargó
                        alert('Sesión expirada. Redirigiendo...');
                    }

                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2500);

                // CASO 2: El error original de 'parsererror' (fallback)
                } else if (textStatus === 'parsererror') {
                    
                    if (typeof showGlobalPopup === 'function') { 
                        showGlobalPopup(
                            'Sesión Expirada', 
                            'Ocurrió un error con las credenciales, redirigiendo...', 
                            'Entendido',
                            'error'
                        );
                    } else {
                        alert('Sesión expirada. Redirigiendo...');
                    }

                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2500);

                // CASO 3: Otro error (ej. 404, 500)
                } else {
                    $('#content-wrap').html(`<p>Error al cargar el contenido: ${xhr.statusText} (${xhr.status})</p>`).fadeTo('fast', 1);
                }
                // --- FIN DE LA CORRECCIÓN ---
            }
        });
    }

    function loadAssets(assets, callback) {
        // Elimina los assets dinámicos de la vista anterior
        $('link[data-dynamic-asset]').remove();
        $('script[data-dynamic-asset]').remove();

        // Carga los nuevos estilos CSS
        if (assets && assets.styles) {
            assets.styles.forEach(function(styleUrl) {
                // Previene recargar estilos ya presentes
                if (!$(`link[href="${styleUrl}"]`).length) {
                    $('<link>', { rel: 'stylesheet', href: styleUrl, 'data-dynamic-asset': true }).appendTo('head');
                }
            });
        }

        // Carga los nuevos scripts JS en secuencia
        let scriptQueue = assets && assets.scripts ? [...assets.scripts] : [];
        function loadNextScript() {
            if (scriptQueue.length === 0) {
                 // --- ELIMINADO ---
                 // Ya no disparamos 'contentLoaded' aquí
                 // --- FIN ELIMINADO ---
                if (callback) callback(); // Llamamos al callback original
                return;
            }
            let scriptUrl = scriptQueue.shift();

            // Evita recargar scripts que ya podrían estar cargados globalmente (como jQuery o DataTables)
            // aunque $.getScript tiene cierta protección contra esto.
            // Más importante, marca el script como dinámico *después* de cargarlo con éxito.
             if ($(`script[src="${scriptUrl}"]`).length > 0 && !$(`script[src="${scriptUrl}"]`).attr('data-dynamic-asset')) {
                 console.log(`Script ${scriptUrl} ya cargado globalmente, omitiendo recarga.`);
                 loadNextScript();
                 return;
             }

            $.getScript(scriptUrl)
                .done(function() {
                    // Marcar como dinámico para poder eliminarlo después
                    $('script[src="'+scriptUrl+'"]').attr('data-dynamic-asset', true);
                    loadNextScript();
                })
                .fail(function() {
                    console.error(`Error al cargar el script: ${scriptUrl}`);
                    loadNextScript(); // Continúa con el siguiente script aunque uno falle
                });
        }
        loadNextScript();
    }

    // --- NUEVA FUNCIÓN GLOBAL PARA INICIALIZAR VISTAS ---
    window.initializeView = function(view) {
        console.log(`Intentando inicializar vista: ${view}`); // Para depuración
        // Llama a la función específica de inicialización si existe
        if (view === 'schedule' && typeof initializeScheduleView === 'function') {
            initializeScheduleView();
        }
        // Añade aquí 'else if' para otras vistas que necesiten inicialización JS
        // else if (view === 'reports' && typeof initializeAvisosView === 'function') {
        //    initializeAvisosView();
        // }
    };
    // --- FIN NUEVA FUNCIÓN ---

    // --- LÓGICA DE CARGA INICIAL (Modificada) ---
    function initializeCurrentView() {
        const initialView = window.location.hash.substring(1);
        if (initialView) {
            loadSection(initialView);
        } else {
            // Carga la vista de inicio por defecto si no hay hash
            // Asegúrate de que 'start' sea la vista correcta
            const defaultView = 'schedule';
            window.location.hash = defaultView; // Establece el hash por defecto
            // loadSection(defaultView); // loadSection se llamará por el evento hashchange
        }
    }

    initializeCurrentView(); // Llama a la función al cargar la página
});