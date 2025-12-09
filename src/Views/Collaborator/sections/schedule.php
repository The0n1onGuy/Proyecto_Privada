<?php
use App\Models\Collaborator\ScheduleModel;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scheduleModel = new ScheduleModel();
$collaboratorPublicId = $_SESSION['user_id'] ?? null;

// 1. Obtener mes y año actuales. El JS se encargará de la navegación.
$currentYear = date('Y');
$currentMonth = date('m');

$startDate = "$currentYear-$currentMonth-01";
$endDate = date("Y-m-t", strtotime($startDate));

// 2. Obtener un resumen de actividades para el mes actual.
$summary = $scheduleModel->getMonthlyActivitySummary($startDate, $endDate, $collaboratorPublicId);

// 3. Formatear los datos para el JS.
$calendarData = [];
foreach ($summary as $item) {
    $date = $item['activity_date']; 
    if (!isset($calendarData[$date])) {
        $calendarData[$date] = [];
    }

    $statusClass = '';
    switch (strtolower($item['status_name'])) {
        case 'completada':
            $statusClass = 'event-success';
            break;
        case 'pendiente':
            $statusClass = 'event-warning';
            break;
        case 'cancelada':
            $statusClass = 'event-danger';
            break;
        default:
            $statusClass = 'event-info';
            break;
    }

    $calendarData[$date][] = [
        'status_name' => htmlspecialchars($item['status_name']),
        'count' => (int) $item['activity_count'],
        'class' => $statusClass,
    ];
}

$jsonCalendarData = json_encode($calendarData);
?>

<!-- Contenedor principal para la vista del calendario -->
<div id="schedule-container" data-calendardata='<?php echo $jsonCalendarData; ?>' class="schedule-wrapper">
    
    <!-- Tarjeta del Calendario -->
    <div class="schedule-card calendar-card">
        <!-- Controles del Calendario (si se necesitan, el JS podría generarlos también) -->
        <div class="calendar-controls">
            <!-- Estos pueden ser manejados por JS si se desea más dinamismo -->
            <button id="prev-month-btn">&laquo; Mes Anterior</button>
            <h2 id="month-year-title" class="month-title"></h2>
            <button id="next-month-btn">Mes Siguiente &raquo;</button>
        </div>

        <!-- Encabezado con días de la semana -->
        <div class="calendar-weekdays">
            <div class="weekday">Domingo</div>
            <div class="weekday">Lunes</div>
            <div class="weekday">Martes</div>
            <div class="weekday">Miércoles</div>
            <div class="weekday">Jueves</div>
            <div class="weekday">Viernes</div>
            <div class="weekday">Sábado</div>
        </div>

        <!-- Grid donde el JS construirá el calendario -->
        <div class="calendar-grid" id="calendar-grid">
            <!-- Las celdas del día se insertarán aquí por JS -->
        </div>
    </div>

    <!-- Tarjeta: Mis actividades de hoy -->
    <div class="schedule-card today-activities-card">
        <h3 class="card-title">Mis actividades de hoy</h3>
        <div id="today-activities" class="today-activities-list">
            <!-- El JS cargará aquí las actividades asignadas al colaborador para hoy -->
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles de las actividades de un día -->
<div id="activity-modal-overlay" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Actividades del Día</h3>
            <button id="modal-close-btn" class="modal-close-btn">&times;</button>
        </div>
        <div id="modal-content-body" class="modal-body">
            <!-- El contenido se cargará aquí por AJAX -->
        </div>
    </div>
</div>

<!-- Inclusión de los scripts necesarios -->
<!-- Asegúrate de que jQuery esté disponible. Si no está global, cárgalo aquí. -->
<!-- <script src="/path/to/your/jquery.min.js"></script> -->
<script src="/js/Collaborator/schedule.js"></script>

<!-- Popup helper markup / estilos para asegurar que los popups funcionen en esta sección -->
<link href="/css/utilities/popup.css" rel="stylesheet">
<div id="resultPopup" class="popup-overlay">
    <div class="popup-content">
        <h2 id="resultPopupTitle" class="popup-title"></h2>
        <p id="resultPopupMessage" class="popup-message"></p>
        <button id="resultPopupCloseBtn" class="popup-close-btn">Entendido</button>
    </div>
</div>
<script src="/js/Utilities/popup.js"></script>

<!-- Inicialización del script del calendario -->
<script>
    // Esperar a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Llamar a la función principal de nuestro script
        initializeScheduleView();
    });
</script>