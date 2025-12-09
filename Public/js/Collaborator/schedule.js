function initializeScheduleView() {
    console.log("Inicializando vista del calendario...");

    const container = document.getElementById('schedule-container');
    if (!container) {
        console.error("Contenedor 'schedule-container' no encontrado.");
        return; 
    }

    const grid = document.getElementById('calendar-grid');
    const modalOverlay = document.getElementById('activity-modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-content-body');
    const modalCloseBtn = document.getElementById('modal-close-btn');

    // Parseo seguro de datos PHP
    let calendarData = {};
    try {
        calendarData = JSON.parse(container.dataset.calendardata || "{}");
    } catch (e) {
        console.error("Error parseando calendarData:", e);
    }

    // --- CONSTRUIR CALENDARIO ---
    function buildCalendar(year, month) {
        grid.innerHTML = ''; 

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        // Ajuste para que Lunes sea el primer día visualmente si quieres (0=Domingo)
        const startOffset = firstDayOfMonth; 

        // Celdas vacías previas
        for (let i = 0; i < startOffset; i++) {
            grid.insertAdjacentHTML('beforeend', '<div class="day-cell empty-cell"></div>');
        }

        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            let dayContent = '';

            // Verificamos si hay datos para este día específico
            if (calendarData[dateStr] && Array.isArray(calendarData[dateStr])) {
                calendarData[dateStr].forEach(item => {
                    // item.class viene del PHP (event-warning, event-success, etc.)
                    dayContent += `
                        <div class="activity-summary-block ${item.class}">
                            <span class="badge-count">${item.count}</span> ${item.status_name}
                        </div>
                    `;
                });
            }

            const cellHTML = `
                <div class="day-cell" data-date="${dateStr}">
                    <div class="day-number">${day}</div>
                    <div class="day-events">
                        ${dayContent}
                    </div>
                </div>`;
            
            grid.insertAdjacentHTML('beforeend', cellHTML);
        }
    }

    // --- FETCH AJAX (Click en el día) ---
    async function fetchAndShowActivities(date) {
        modalBody.innerHTML = '<div class="loading-spinner">Cargando actividades...</div>';
        modalTitle.innerText = `Actividades del ${date}`;
        modalOverlay.style.display = 'flex';

        try {
            const formData = new URLSearchParams();
            formData.append('fecha', date); // Coincide con $_POST['fecha']

            const response = await fetch('/collaborator/getActivitiesForDate', { // Ruta correcta
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            if (!response.ok) throw new Error('Error de red al cargar datos.');
            
            const activities = await response.json();
            
            // Validar si devolvió error de PHP
            if (activities.error) throw new Error(activities.error);

            populateModal(activities);

        } catch (error) {
            console.error(error);
            modalBody.innerHTML = `<p class="error-msg">No se pudieron cargar las actividades.</p>`;
        }
    }

    // --- PINTAR MODAL ---
    function populateModal(activities) {
        if (!activities || activities.length === 0) {
            modalBody.innerHTML = '<p class="no-data">No hay actividades programadas para este día.</p>';
            return;
        }

        let html = '<ul class="activity-list">';
        activities.forEach(act => {
            // Diseño limpio sin horarios, solo QUÉ y QUIÉN
            html += `
                <li class="activity-item">
                    <div class="act-header">
                        <span class="act-title">${act.nombre_actividad}</span>
                        <span class="act-status pill">${act.nombre_estatus}</span>
                    </div>
                    <div class="act-details">
                        <p><strong>Ubicación:</strong> ${act.nombre_privada}</p>
                        <p><strong>Responsable:</strong> ${act.nombre_responsable}</p>
                        <p><strong>Servicio:</strong> ${act.nombre_servicio || 'General'}</p>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        modalBody.innerHTML = html;
    }

    // --- LISTENERS ---
    $(grid).off('click').on('click', '.day-cell', function() {
        if ($(this).hasClass('empty-cell')) return;
        const date = $(this).data('date');
        fetchAndShowActivities(date);
    });

    const closeModal = () => {
        modalOverlay.style.display = 'none';
        modalBody.innerHTML = '';
    };

    $(modalCloseBtn).off('click').on('click', closeModal);
    $(modalOverlay).off('click').on('click', (e) => {
        if (e.target === modalOverlay) closeModal();
    });

    // Inicializar hoy
    const now = new Date();
    buildCalendar(now.getFullYear(), now.getMonth());
}