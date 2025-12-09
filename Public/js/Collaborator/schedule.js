function initializeScheduleView() {
    console.log("Inicializando vista del calendario...");

    // --- SELECTORES DE ELEMENTOS ---
    const container = document.getElementById('schedule-container');
    if (!container) {
        console.error("Contenedor 'schedule-container' no encontrado.");
        return; 
    }

    // Elementos para la navegación y título (se asume que existen en el HTML)
    const monthYearTitle = document.getElementById('month-year-title');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');

    if (!monthYearTitle || !prevMonthBtn || !nextMonthBtn) {
        console.warn("Elementos de navegación del calendario (título o botones) no encontrados. La navegación no funcionará.");
    }

    // Elementos del calendario y modal
    const grid = document.getElementById('calendar-grid');
    const modalOverlay = document.getElementById('activity-modal-overlay');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-content-body');
    const modalCloseBtn = document.getElementById('modal-close-btn');

    // Parseo seguro de datos PHP
    // `calendarData` ahora se puede actualizar, por eso se declara con `let`
    let calendarData = {};
    try {
        calendarData = JSON.parse(container.dataset.calendardata || "{}");
    } catch (e) {
        console.error("Error parseando calendarData:", e);
    }

    // --- ESTADO DEL CALENDARIO ---
    // Se inicializa con la fecha actual, pero se actualizará con la navegación
    let currentDate = new Date();
    const initialDate = new Date(); // Guardar fecha inicial para validación de 3 años

    // --- CONSTRUIR CALENDARIO ---
    function buildCalendar(year, month) {
        // Actualizar el estado actual
        currentDate.setFullYear(year);
        currentDate.setMonth(month);

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

    // --- NAVEGACIÓN Y CARGA DE DATOS AJAX ---
    async function fetchCalendarData(year, month) {
        // Muestra un indicador de carga si lo deseas
        container.classList.add('loading');

        try {
            // El mes en la URL debe ser 1-indexado
            const response = await fetch(`/collaborator/getScheduleForMonth?year=${year}&month=${month + 1}`);
            if (!response.ok) throw new Error('Error de red al cargar el mes.');

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            // Actualizar datos y vista
            calendarData = data.calendarData;
            if (monthYearTitle) {
                monthYearTitle.textContent = `${data.monthName} ${data.year}`;
            }
            buildCalendar(year, month);

        } catch (error) {
            console.error("Error al cargar datos del calendario:", error);
            if (monthYearTitle) {
                monthYearTitle.textContent = "Error al cargar";
            }
        } finally {
            container.classList.remove('loading');
        }
    }

    // --- FUNCIÓN PARA OBTENER NOMBRE DEL MES ---
    function getMonthName(month) {
        const months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        return months[month];
    }

    // --- FUNCIÓN PARA ACTUALIZAR TÍTULO DEL MES ---
    function updateMonthTitle(year, month) {
        if (monthYearTitle) {
            const monthName = getMonthName(month);
            monthYearTitle.textContent = `${monthName} ${year}`;
        }
    }

    // --- FETCH AJAX (Click en el día) ---
    async function fetchAndShowActivities(date) {
        modalBody.innerHTML = '<div class="loading-spinner">Cargando actividades...</div>';
        modalTitle.innerText = `Actividades del ${date}`;
        modalOverlay.style.display = 'flex';

        // CORRECCIÓN: Cambiado a GET y usando el parámetro 'date'
        try {
            const response = await fetch(`/collaborator/getActivitiesForDate?date=${date}`);
            if (!response.ok) throw new Error('Error de red al cargar datos.');
            
            const data = await response.json();
            
            // Validar si devolvió un objeto con error (no es un array)
            if (data && typeof data === 'object' && !Array.isArray(data) && data.error) {
                throw new Error(data.error);
            }

            // Si no es un array, hay un problema
            if (!Array.isArray(data)) {
                throw new Error('Formato de respuesta inválido del servidor.');
            }

            populateModal(data);

        } catch (error) {
            console.error("Error en fetchAndShowActivities:", error);
            modalBody.innerHTML = `<p class="error-msg">No se pudieron cargar las actividades. Detalle: ${error.message}</p>`;
        }
    }

    // --- FUNCIÓN PARA MOSTRAR FORMULARIO DE REPORTE ---
    function showReportForm(programadaId) {
        // Evitar insertar el formulario dentro del modal de actividades.
        // Creamos un modal overlay independiente para el reporte y lo insertamos en body.
        const overlayId = `report-modal-overlay-${programadaId}`;

        // Si ya existe, no crear duplicados
        if (document.getElementById(overlayId)) return;

        const reportOverlay = document.createElement('div');
        reportOverlay.id = overlayId;
        reportOverlay.className = 'modal-overlay';
        reportOverlay.style.display = 'flex';

        // IDs únicos para inputs para evitar colisiones cuando hay varias actividades
        const descId = `desc-ejecucion-${programadaId}`;
        const huboId = `hubo-incidencia-${programadaId}`;
        const incidId = `desc-incidencia-${programadaId}`;
        const formId = `report-form-${programadaId}`;

        reportOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Levantar Reporte</h3>
                    <button class="modal-close-btn" data-overlay-id="${overlayId}">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="${formId}">
                        <div class="form-group">
                            <label for="${descId}">Descripción de la Ejecución:</label>
                            <textarea id="${descId}" name="descripcion_ejecucion" placeholder="Describe cómo se ejecutó la actividad..." required></textarea>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="${huboId}" name="hubo_incidencia" value="1">
                            <label for="${huboId}">¿Hubo alguna incidencia?</label>
                        </div>

                        <div class="form-group" id="${incidId}-container" style="display: none;">
                            <label for="${incidId}">Descripción de la Incidencia:</label>
                            <textarea id="${incidId}" name="descripcion_incidencia" placeholder="Describe la incidencia ocurrida..."></textarea>
                        </div>

                        <div class="form-buttons">
                            <button type="button" class="btn-cancel" data-overlay-id="${overlayId}">Cancelar</button>
                            <button type="submit" class="btn-submit">Guardar Reporte</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(reportOverlay);

        // Scoped jQuery handlers using the specific form/ids
        const $form = $(`#${formId}`);

        // Mostrar/ocultar campo de incidencia
        $(`#${huboId}`).off('change').on('change', function() {
            if (this.checked) {
                $(`#${incidId}-container`).show();
            } else {
                $(`#${incidId}-container`).hide();
            }
        });

        // Cerrar overlay (botón close / cancelar)
        $(`#${overlayId} .modal-close-btn, #${overlayId} .btn-cancel`).off('click').on('click', function() {
            const oid = $(this).data('overlay-id');
            const el = document.getElementById(oid);
            if (el) el.parentNode.removeChild(el);
        });

        // Envío del formulario (scoped)
        $form.off('submit').on('submit', function(e) {
            e.preventDefault();

            // Recolectar valores desde el formulario específico
            const descEjecucion = $form.find('[name="descripcion_ejecucion"]').val();
            const huboIncidencia = $form.find('[name="hubo_incidencia"]').is(':checked') ? 1 : 0;
            const descIncidencia = $form.find('[name="descripcion_incidencia"]').val() || null;

            // Llamar al envío con los valores ya obtenidos
            submitReport(programadaId, { descEjecucion, huboIncidencia, descIncidencia, overlayId });
        });
    }

    // --- FUNCIÓN PARA ENVIAR REPORTE ---
    async function submitReport(programadaId, values) {
        // values: { descEjecucion, huboIncidencia, descIncidencia, overlayId }
        const descEjecucion = values.descEjecucion;
        const huboIncidencia = values.huboIncidencia;
        const descIncidencia = values.descIncidencia;
        const overlayId = values.overlayId;

        try {
            const response = await fetch('/collaborator/createActivityReport', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    programada_id: programadaId,
                    descripcion_ejecucion: descEjecucion,
                    hubo_incidencia: huboIncidencia,
                    descripcion_incidencia: descIncidencia
                })
            });

            const result = await response.json();

            if (!response.ok || result.error) {
                throw new Error(result.error || 'Error al guardar el reporte');
            }

            // Mostrar popup de éxito usando helper global
            if (typeof showGlobalPopup === 'function') {
                showGlobalPopup('Éxito', 'Reporte guardado exitosamente', 'Entendido', 'success', 'resultPopup');
            } else {
                alert('Reporte guardado exitosamente');
            }

            // Cerrar el overlay del reporte si existe
            if (overlayId) {
                const el = document.getElementById(overlayId);
                if (el) el.parentNode.removeChild(el);
            }

            // También limpiar cualquier formulario agregado previamente en el modalBody por seguridad
            modalBody.querySelectorAll('.report-form, form[id^="report-form-"]').forEach(n => n.remove());

            // Opcional: cerrar el modal de actividades también (comenta si prefieres dejarlo abierto)
            closeModal();

            // Refrescar la lista de 'Mis actividades de hoy' para reflejar cambios
            try {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                const todayStr = `${y}-${m}-${d}`;
                fetchAndRenderTodayActivities(todayStr);
            } catch (e) {
                console.warn('No se pudo refrescar la lista de hoy:', e);
            }

        } catch (error) {
            console.error("Error en submitReport:", error);
            if (typeof showGlobalPopup === 'function') {
                showGlobalPopup('Error', `Error al guardar el reporte: ${error.message}`, 'Entendido', 'error', 'resultPopup');
            } else {
                alert(`Error al guardar el reporte: ${error.message}`);
            }
        }
    }

    // --- PINTAR MODAL ---
    function populateModal(activities) {
        if (!activities || activities.length === 0) {
            modalBody.innerHTML = '<p class="no-data">No hay actividades programadas para este día.</p>';
            return;
        }

        let html = '<ul class="activity-list">';
        activities.forEach((act, index) => {
            // Determinar clase de estatus para la pill
            const statusLower = (act.nombre_estatus || '').toLowerCase();
            let statusClass = '';
            if (statusLower.indexOf('pendiente') !== -1) statusClass = 'pendiente';
            if (statusLower.indexOf('completada') !== -1) statusClass = 'completada';
            if (statusLower.indexOf('cancelada') !== -1) statusClass = 'cancelada';
            if (statusLower.indexOf('proceso') !== -1) statusClass = 'proceso';

            html += `
                <li class="activity-item" data-activity-id="${act.programada_id}" data-activity-index="${index}" data-activity-date="${act.fecha_programada}">
                    <div class="act-header">
                        <span class="act-title">${act.nombre_actividad}</span>
                        <span class="act-status pill ${statusClass}">${act.nombre_estatus}</span>
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

    // --- RENDER 'MIS ACTIVIDADES DE HOY' ---
    async function fetchAndRenderTodayActivities(date) {
        const container = document.getElementById('today-activities');
        if (!container) return;

        container.innerHTML = '<div class="loading-spinner">Cargando actividades de hoy...</div>';

        try {
            const response = await fetch(`/collaborator/getActivitiesForDate?date=${date}`);
            if (!response.ok) throw new Error('Error de red al cargar actividades de hoy.');
            const data = await response.json();
            if (data && data.error) throw new Error(data.error);
            if (!Array.isArray(data)) throw new Error('Respuesta inválida del servidor.');

            renderTodayActivities(data);
        } catch (err) {
            console.error('Error cargando actividades de hoy:', err);
            container.innerHTML = `<p class="error-msg">No se pudieron cargar las actividades de hoy. Detalle: ${err.message}</p>`;
        }
    }

    function renderTodayActivities(activities) {
        const container = document.getElementById('today-activities');
        if (!container) return;

        if (!activities || activities.length === 0) {
            container.innerHTML = '<p class="no-data">No tienes actividades asignadas para hoy.</p>';
            return;
        }

        let html = '<div class="today-list">';
        activities.forEach(act => {
            const statusLower = (act.nombre_estatus || '').toLowerCase();
            let statusClass = '';
            if (statusLower.indexOf('pendiente') !== -1) statusClass = 'pendiente';
            if (statusLower.indexOf('completada') !== -1) statusClass = 'completada';
            if (statusLower.indexOf('cancelada') !== -1) statusClass = 'cancelada';
            if (statusLower.indexOf('proceso') !== -1) statusClass = 'proceso';

            const activityDate = act.fecha_programada ? new Date(act.fecha_programada + 'T00:00:00') : null;
            const today = new Date(); today.setHours(0,0,0,0);
            const isPast = activityDate ? (activityDate < today) : false;
            const isCompleted = statusLower.indexOf('completada') !== -1;
            const shouldDisableReport = isPast || isCompleted;

            const reportButton = shouldDisableReport
                ? `<button class="report-btn" disabled title="No se permiten reportes para fechas pasadas o actividades completadas">Levantar Reporte</button>`
                : `<button class="report-btn" data-programada-id="${act.programada_id}">Levantar Reporte</button>`;

            html += `
                <div class="today-activity-card" data-activity-id="${act.programada_id}">
                    <div class="card-header">
                        <h4 class="act-title">${act.nombre_actividad}</h4>
                        <span class="act-status pill ${statusClass}">${act.nombre_estatus}</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Ubicación:</strong> ${act.nombre_privada}</p>
                        <p><strong>Responsable:</strong> ${act.nombre_responsable}</p>
                    </div>
                    <div class="card-actions">${reportButton}</div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;

        // Agregar listeners a los botones de reporte en la lista de hoy
        $(container).off('click').on('click', '.report-btn', function(e) {
            e.preventDefault();
            if ($(this).is(':disabled')) {
                if (typeof showGlobalPopup === 'function') {
                    showGlobalPopup('Acción no permitida', 'No se permiten reportes para actividades con fecha anterior a hoy o actividades ya completadas.', 'Entendido', 'alert', 'resultPopup');
                } else {
                    alert('No se permiten reportes para actividades con fecha anterior a hoy.');
                }
                return;
            }
            const programadaId = $(this).data('programada-id');
            showReportForm(programadaId);
        });
    }

    // --- LISTENERS ---
    // Click en un día para ver detalles
    $(grid).off('click').on('click', '.day-cell', function() {
        if ($(this).hasClass('empty-cell')) return;
        const date = $(this).data('date');
        fetchAndShowActivities(date);
    });

    // Navegación de meses
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', () => {
            // Verificar que no nos movemos más de 3 años atrás
            const testDate = new Date(currentDate);
            testDate.setMonth(testDate.getMonth() - 1);
            
            const yearDiff = initialDate.getFullYear() - testDate.getFullYear();
            
            if (yearDiff > 3) {
                if (typeof showGlobalPopup === 'function') {
                    showGlobalPopup('Límite alcanzado', 'No puedes navegar más de 3 años atrás', 'Entendido', 'alert', 'resultPopup');
                } else {
                    alert('No puedes navegar más de 3 años atrás');
                }
                return;
            }
            
            currentDate.setMonth(currentDate.getMonth() - 1);
            fetchCalendarData(currentDate.getFullYear(), currentDate.getMonth());
        });
    }
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', () => {
            // Verificar que no nos movemos más de 3 años adelante
            const testDate = new Date(currentDate);
            testDate.setMonth(testDate.getMonth() + 1);
            
            const yearDiff = testDate.getFullYear() - initialDate.getFullYear();
            
            if (yearDiff > 3) {
                if (typeof showGlobalPopup === 'function') {
                    showGlobalPopup('Límite alcanzado', 'No puedes navegar más de 3 años adelante', 'Entendido', 'alert', 'resultPopup');
                } else {
                    alert('No puedes navegar más de 3 años adelante');
                }
                return;
            }
            
            currentDate.setMonth(currentDate.getMonth() + 1);
            fetchCalendarData(currentDate.getFullYear(), currentDate.getMonth());
        });
    }

    // Cierre del modal
    const closeModal = () => {
        modalOverlay.style.display = 'none';
        modalBody.innerHTML = '';
    };

    $(modalCloseBtn).off('click').on('click', closeModal);
    $(modalOverlay).off('click').on('click', (e) => {
        if (e.target === modalOverlay) closeModal();
    });

    // --- INICIALIZACIÓN ---
    // Usamos los datos pasados desde PHP para la carga inicial
    const initialYear = parseInt(container.dataset.year, 10) || new Date().getFullYear();
    const initialMonthName = container.dataset.monthname || "Mes";
    const initialJSMonth = new Date().getMonth(); // Mes actual 0-indexado
    
    // Establecemos el título inicial con el mes y año
    updateMonthTitle(initialYear, initialJSMonth);

    // Construimos el calendario con la fecha del servidor
    buildCalendar(initialYear, initialJSMonth);

    // Cargar y renderizar las actividades asignadas para hoy en la sección correspondiente
    try {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const todayStr = `${y}-${m}-${d}`;
        fetchAndRenderTodayActivities(todayStr);
    } catch (e) {
        console.warn('No se pudo cargar las actividades de hoy automáticamente:', e);
    }
}