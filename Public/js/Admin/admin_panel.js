document.addEventListener('DOMContentLoaded', function() {
    fetch('/admin/privadas/list')
        .then(response => response.json())
        .then(data => {
            const menu = document.getElementById('privateMenu');
            const defaultImage = '/images/default_privada.png';
            menu.innerHTML = ''; 

            data.forEach(privada => {
                const nombreArchivo = privada.nombre.toLowerCase().replace(/\s+/g, '_') + '.jpg';
                const rutaImagen = `/images/privadas/${nombreArchivo}`;

                const form = document.createElement('form');
                form.action = '/admin/set-private';
                form.method = 'post';
                form.classList.add('private-option');

                form.innerHTML = `
                    <input type="hidden" name="id_privada" value="${privada.id_privada}">
                    <button type="submit" class="private-option-btn">
                        <img src="${rutaImagen}" onerror="this.src='${defaultImage}'" class="private-avatar">
                        <span>${privada.nombre}</span>
                    </button>
                `;

                menu.appendChild(form);
            });
        })
        .catch(err => {
            console.error('Error al cargar privadas:', err);
        });
    // --- ELEMENTOS DEL DOM ---
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const topbar = document.getElementById('topbar');
    const contentWrap = document.getElementById('content-wrap');
    const logoutButton = document.getElementById('logout-button');
    const logoutPopup = document.getElementById('logoutPopup');
    const resultPopup = document.getElementById('resultPopup');
    const resultPopupTitle = document.getElementById('resultPopupTitle');
    const resultPopupMessage = document.getElementById('resultPopupMessage');
    const resultPopupCloseBtn = document.getElementById('resultPopupCloseBtn');

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
    $('.nav-link').on('click', function(e) {
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
    
    
    
    // --- FUNCIONES DE CARGA Y RENDERIZADO ---

    function loadSection(view, params = '') {
        // Marca el enlace activo en el menú lateral
        $('.nav-link').removeClass('active');
        $(`.nav-link[data-view="${view}"]`).addClass('active');

        const url = `/admin/content/${view}${params}`;
        $('#content-wrap').fadeTo('fast', 0.3);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.privada_info) {
                    $('#privateName').text(response.privada_info.name);
                    $('#privateAvatar').attr('src', response.privada_info.image);
                    // Fallback por si la imagen no carga
                    $('#privateAvatar').on('error', function() {
                        $(this).attr('src', '/images/default_privada.png');
                    });
                }
                $('#content-wrap').html(response.html);
                loadAssets(response.assets, function() {
                    initializeViewPlugins(view); 
                    
                    // if (typeof initializeView === 'function') {
                    //     initializeView(); 
                    // }
                    
                    $('#content-wrap').fadeTo('fast', 1);
                });
            },
            error: function(xhr, textStatus, errorThrown) {
                
                // 1. Ahora 'textStatus' SÍ existe y podemos compararlo
                if (xhr.status === 401) {
                    showResultPopup(
                        'Sesión Expirada', 
                        'Tu sesión ha caducado. Serás redirigido al inicio.', 
                        'error' // Esto aplica la clase de error al popup
                    );
                    
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2500); // 2.5 segundos para leer el mensaje

                // CASO 2: El error original de 'parsererror' (fallback)
                } else if (textStatus === 'parsererror') {
                    showResultPopup(
                        'Sesión Expirada', 
                        'Ocurrió un error con las credenciales, redirigiendo...', 
                        'error' 
                    );

                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2500);

                // CASO 3: Otro error (ej. 404, 500)
                } else {
                    // --- INICIO DE LA DEPURACIÓN ---
                    console.error("Respuesta de error inesperado:", {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    // --- FIN DE LA DEPURACIÓN ---
                    
                    const errorMsg = `Error: ${xhr.status} (${xhr.statusText})`;
                    $('#content-wrap').html(`<p>Error al cargar contenido. Revisa la consola (F12). ${errorMsg}</p>`).fadeTo('fast', 1);
                }
            }
        });
    }

    // --- FUNCIONES PARA EL MODAL DE RESULTADOS ---
    
    window.showResultPopup = function(title, message, type = 'success') {
        if (!resultPopup) return;
        resultPopupTitle.textContent = title;
        resultPopupMessage.textContent = message;
        const content = resultPopup.querySelector('.popup-content');
        content.className = 'popup-content';
        content.classList.add(type);
        resultPopup.classList.add('visible');
    }

    // function hideResultPopup() {
    //     if (!resultPopup) return;
    //     resultPopup.classList.remove('visible');
    // }

    if (resultPopup) {
        resultPopupCloseBtn.addEventListener('click', () => resultPopup.classList.remove('visible'));
        resultPopup.addEventListener('click', (event) => {
            if (event.target === resultPopup) { 
                resultPopup.classList.remove('visible');
            }
        });
    }

    function loadAssets(assets, callback) {
        $('link[data-dynamic-asset]').remove();
        $('script[data-dynamic-asset]').remove();

        if (assets && assets.styles) {
            assets.styles.forEach(function(styleUrl) {
                if (!$(`link[href="${styleUrl}"]`).length) {
                    $('<link>', { rel: 'stylesheet', href: styleUrl, 'data-dynamic-asset': true }).appendTo('head');
                }
            });
        }

        let scriptQueue = assets && assets.scripts ? [...assets.scripts] : [];
        function loadNextScript() {
            if (scriptQueue.length === 0) {
                if (callback) callback();
                return;
            }
            let scriptUrl = scriptQueue.shift();
            
            $.getScript(scriptUrl)
                .done(function() {
                    $('script[src="'+scriptUrl+'"]').attr('data-dynamic-asset', true);
                    loadNextScript();
                })
                .fail(function() {
                    console.error(`Error al cargar el script: ${scriptUrl}`);
                    loadNextScript();
                });
        }
        loadNextScript();
    }
    
    // --- INICIALIZACIÓN DE PLUGINS POR SECCIÓN ---

    function initializeViewPlugins(view) {
        switch(view) {
            case 'dashboard':
                initializeDashboardChart();
                break;
            case 'residents':
                initializeResidentsTable();
                if (typeof initializeView === 'function') {
                        initializeView(); 
                }
                break;
            case 'colaboradores':
                initializeColaboradoresTable();
                break;
             case 'servicios':
                initializeServiciosTable();
                break;
            case 'visitas':
                initializeVisitasTable();
                break;
        }
        
    }
    function initializeDashboardChart() {
        if (typeof pagosResumen === 'undefined') {
        console.error('Datos del gráfico (pagosResumen) no encontrados.');
        return; 
    }
        const ctx = document.getElementById('pagosChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completado', 'Pendiente', 'Moroso'],
                    datasets: [{
                        data: [pagosResumen.completado, pagosResumen.pendiente, pagosResumen.moroso],
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.8)', // Indigo-500
                            'rgba(251, 191, 36, 0.8)', // Amber-400
                            'rgba(239, 68, 68, 0.8)'  // Red-500
                        ],
                        borderColor: [
                            'rgba(79, 70, 229, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 14 },
                                color: '#374151'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' pagos';
                                }
                            },
                            backgroundColor: 'rgba(30, 41, 59, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            borderRadius: 8
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1500
                    }
                }
            });
    }
    function initializeResidentsTable() {
        $('#tablaResidentes').DataTable({
            "destroy": true, 
            "language": { "url": "/js/Utilities/spanish.json" },
            "order": [[5, "asc"], [2, "desc"]]
        });
    }
    // Manejador para el filtro de residentes
    $(document).on('change', '#residentFilter', function() {
        var filterValue = $(this).val();
        loadSection('residents', `?filter=${filterValue}`);
    });
    function initializeColaboradoresTable() {
         $('#tablaColaboradores').DataTable({
            "destroy": true,
            "language": { "url": "/js/Utilities/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
    }
    function initializeServiciosTable() {
        $('#tablaProveedores').DataTable({
            "destroy": true,
            "language": { "url": "/js/Utilities/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
        $('#tablaServicios').DataTable({
            "destroy": true,
            "language": { "url": "/js/Utilities/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
        
    }
    // Manejador para el filtro de proveedores
    $(document).on('change', '#proveedorFilter', function() {
        var filterValue = $(this).val();
        loadSection('servicios', `?filter=${filterValue}`);
    });
    function initializeVisitasTable() {
         $('#tablaVisitas').DataTable({
            "destroy": true,
            "language": { "url": "/js/Utilities/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
    }

    // --- LÓGICA DE CARGA INICIAL ---
    
    // Al cargar la página, comprueba si hay un hash y carga esa sección.
    // Si no, carga el dashboard por defecto.
    function initializeCurrentView() {
        const initialView = window.location.hash.substring(1) || 'dashboard';
        if (window.location.hash === '') {
            window.location.hash = initialView; // Set the hash if it's not present
        }else if(initialView) {
            loadSection(initialView);
        } else {
            // Si no hay hash, activa el enlace del dashboard y carga sus plugins
            $('.nav-link[data-view="dashboard"]').addClass('active');
            initializeViewPlugins('dashboard');
        }
    }
    
    initializeCurrentView();
});