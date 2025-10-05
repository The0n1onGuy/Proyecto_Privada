// public/js/admin_dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENTOS DEL DOM (sin cambios) ---
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const topbar = document.getElementById('topbar');
    const contentWrap = document.getElementById('content-wrap');
    const logoutButton = document.getElementById('logout-button');
    const logoutPopup = document.getElementById('logoutPopup');

    // --- FUNCIONALIDAD DEL MENÚ Y LOGOUT (sin cambios) ---
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

    // --- LÓGICA DE CARGA DE CONTENIDO (actualizada) ---
    $('.nav-link').on('click', function(e) {
        const href = $(this).attr('href');
        if (href === '/logout') return;
        
        e.preventDefault();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');

        const view = $(this).data('view');
        
        if (view) {
            const url = `/admin/content/${view}`;
            
            // Usamos $.ajax para manejar la respuesta JSON
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // 1. Limpiamos assets anteriores y cargamos los nuevos
                    loadAssets(response.assets, function() {
                        // 2. Insertamos el nuevo HTML
                        $('#content-wrap').html(response.html);
                        // 3. Inicializamos los plugins para la nueva vista
                        initializePluginsForView(view);
                    });
                },
                error: function(xhr) {
                    $('#content-wrap').html(`<p>Error al cargar el contenido: ${xhr.statusText}</p>`);
                }
            });
        }
    });

    /**
     * Carga dinámicamente los archivos CSS y JS necesarios para una sección.
     * @param {object} assets - Objeto con arrays de 'styles' y 'scripts'.
     * @param {function} callback - Función a ejecutar cuando todos los scripts se hayan cargado.
     */
    function loadAssets(assets, callback) {
        // Limpiar estilos de secciones anteriores
        $('link[data-dynamic-asset]').remove();
        
        // Cargar nuevos estilos
        if (assets.styles) {
            assets.styles.forEach(function(styleUrl) {
                if (!$(`link[href="${styleUrl}"]`).length) {
                    $('<link>', {
                        rel: 'stylesheet',
                        type: 'text/css',
                        href: styleUrl,
                        'data-dynamic-asset': true // Marca para poder eliminarlo después
                    }).appendTo('head');
                }
            });
        }

        // Cargar nuevos scripts secuencialmente
        let scriptQueue = assets.scripts ? [...assets.scripts] : [];
        function loadNextScript() {
            if (scriptQueue.length === 0) {
                if (callback) callback();
                return;
            }
            let scriptUrl = scriptQueue.shift();
            
            // No recargar scripts que ya están en la página (como Chart.js si se navega varias veces)
            if ($(`script[src="${scriptUrl}"]`).length) {
                loadNextScript();
                return;
            }

            $.getScript(scriptUrl)
                .done(function() {
                    loadNextScript();
                })
                .fail(function() {
                    console.error(`Error al cargar el script: ${scriptUrl}`);
                    loadNextScript(); // Continuar con el siguiente aunque uno falle
                });
        }
        loadNextScript();
    }

    /**
     * Inicializa los plugins (gráficas, tablas, etc.) para una vista específica.
     */
    function initializePluginsForView(view) {
        if (view === 'dashboard') {
            initializeDashboardChart();
            CargaDataTablesDashboard();
        } else if (view === 'users') {
            // Lógica que estaba en el <script> de users.php
            $('#tablaUsuarios').DataTable({
                "language": {
                    "url": "/js/spanish.json"
                }
            });
        } else if (view === 'privadas') {
            // Lógica que estaba en el <script> de users.php

        }
    }

    /**
     * Dibuja la gráfica del dashboard.
     * Esta es la lógica que antes estaba en admin_content.js.
     */
    function initializeDashboardChart() {
        const ctx = document.getElementById('myChart');
        if (!ctx || typeof Chart === 'undefined') return; // Si no hay canvas o Chart.js no está cargado, no hacer nada.

        // La variable `paymentDataFromPHP` se crea en `dashboard.php` y está disponible globalmente.
        if (typeof paymentDataFromPHP === 'undefined') {
            console.error('Los datos para la gráfica (paymentDataFromPHP) no están disponibles.');
            return;
        }
        
        const labels = paymentDataFromPHP.map(item => item.metodo_pago);
        const dataValues = paymentDataFromPHP.map(item => item.total);

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos por Método de Pago',
                    data: dataValues,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 206, 86, 0.5)', 'rgba(255, 99, 132, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)', 'rgba(255, 99, 132, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // --- INICIALIZACIÓN EN LA CARGA DE PÁGINA ---
    // Comprobamos si el canvas del dashboard ya existe en la página al cargar.
    // Si es así, significa que hemos aterrizado directamente en el dashboard.
    if (document.getElementById('myChart')) {
        initializeDashboardChart();
    }
    if (document.getElementById('tablaUsuarios')) {
        $('#tablaUsuarios').DataTable({
            "language": { "url": "/js/spanish.json" }
        });
    }

    // TEMPORAL DEDICARSELO dashboardContent.js
    CargaDataTablesDashboard();
    function CargaDataTablesDashboard(){
        if (document.getElementById('tablaReportes')) {
        $('#tablaReportes').DataTable({
            "language": { "url": "/js/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
    }
    if (document.getElementById('tablaAvisos')) {
        $('#tablaAvisos').DataTable({
            "language": { "url": "/js/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
    }
    }
    
});