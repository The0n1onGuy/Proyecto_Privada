function initializeScheduleView() {
    console.log("Inicializando vista del calendario..."); // Para depuración

    // --- 1. CONFIGURACIÓN INICIAL ---
    const container = document.getElementById('schedule-container');
    if (!container) {
        console.log("Contenedor de calendario no encontrado.");
        return; 
    }

    const grid = document.getElementById('calendar-grid');
    const modalOverlay = document.getElementById('activity-modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-content-body');
    const modalCloseBtn = document.getElementById('modal-close-btn');

    // Cargar los datos del PHP
    // Usamos '|| "[]"' como fallback seguro por si 'dataset.calendardata' está vacío
    const calendarData = JSON.parse(container.dataset.calendardata || "[]");

    // --- 2. FUNCIÓN PARA DIBUJAR EL CALENDARIO ---
    function buildCalendar(year, month) {
        grid.innerHTML = ''; // Limpiar el grid

        const firstDayOfMonth = new Date(year, month, 1).getDay(); // 0=Domingo, 1=Lunes
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const startOffset = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < startOffset; i++) {
            grid.insertAdjacentHTML('beforeend', '<div class="day-cell empty-cell"></div>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            let dayCellHTML = `<div class="day-cell" data-date="${date}">
                                <div class="day-number">${day}</div>`;

            if (calendarData[date]) {
                for (const estatus in calendarData[date]) {
                    const data = calendarData[date][estatus];
                    dayCellHTML += `<div class="activity-summary-block status-${estatus}">
                                        ${data.count} ${estatus}
                                    </div>`;
                }
            }

            dayCellHTML += `</div>`;
            grid.insertAdjacentHTML('beforeend', dayCellHTML);
        }
    }

    // --- 3. FUNCIONES PARA EL MODAL Y AJAX ---
    async function fetchAndShowActivities(date) {
        modalBody.innerHTML = '<p>Cargando actividades...</p>';
        modalTitle.innerText = `Actividades del ${date}`;
        modalOverlay.style.display = 'flex';

        try {
            const formData = new URLSearchParams();
            formData.append('fecha', date);

            const response = await fetch('/collaborator/getActivitiesForDate', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al cargar los datos.');
            }

            const activities = await response.json();
            populateModal(activities);

        } catch (error) {
            modalBody.innerHTML = `<p>Ocurrió un error: ${error.message}</p>`;
        }
    }

    function populateModal(activities) {
        if (activities.length === 0) {
            modalBody.innerHTML = '<p>No hay actividades programadas para esta fecha.</p>';
            return;
        }

        let modalHTML = '';
        activities.forEach(act => {
            modalHTML += `
                <div class="activity-detail-item">
                    <strong>${act.nombre_actividad}</strong> (${act.nombre_servicio})<br>
                    <strong>Privada:</strong> ${act.nombre_privada}<br>
                    <strong>Responsable:</strong> ${act.nombre_responsable || 'No asignado'}<br>
                    <strong>Estatus:</strong> ${act.nombre_estatus}<br>
                    <strong>Tiene Reporte:</strong> ${act.tiene_reporte}
                </div>
            `;
        });
        modalBody.innerHTML = modalHTML;
    }

    // --- 4. EVENT LISTENERS ---
    
    // Usamos .off().on() para evitar duplicar listeners en recargas AJAX
    $(grid).off('click').on('click', (e) => {
        const dayCell = e.target.closest('.day-cell');
        if (!dayCell || dayCell.classList.contains('empty-cell')) {
            return;
        }
        const date = dayCell.dataset.date;
        fetchAndShowActivities(date);
    });

    const closeModal = () => {
        modalOverlay.style.display = 'none';
        modalBody.innerHTML = '';
    };

    $(modalCloseBtn).off('click').on('click', closeModal);
    $(modalOverlay).off('click').on('click', (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    // --- 5. INICIALIZACIÓN ---
    const today = new Date();
    buildCalendar(today.getFullYear(), today.getMonth());

}