<?php
// Incluir el modelo (asumiendo que ya está inicializado y disponible)
use App\Models\Collaborator\ScheduleModel;
$scheduleModel = new ScheduleModel();

// 1. Obtener mes y año de la URL o usar el actual por defecto.
// Usamos el operador de coalescencia nula (??) para versiones recientes de PHP (7.4+)
// y filtramos para asegurar que son valores enteros.
$currentMonth = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT) ?? date('m');
$currentYear = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT) ?? date('Y');

// Validar que el mes y el año estén dentro de un rango razonable
if ($currentMonth < 1 || $currentMonth > 12) {
    $currentMonth = date('m');
}
if ($currentYear < 2000 || $currentYear > 2099) {
    $currentYear = date('Y');
}

// 2. Obtener los horarios para el mes y año seleccionados.
// Necesitarás actualizar ScheduleModel.php para soportar esto (ver paso 3).
$schedule = $scheduleModel->getMonthlySchedule($currentYear, $currentMonth);
$scheduledDays = [];
foreach ($schedule as $event) {
    // Almacenar el día del mes y la información de la cita.
    // Usamos DateTimeImmutable para un manejo de fechas moderno y seguro.
    try {
        $dateTime = new DateTimeImmutable($event['start_time']);
        $dayOfMonth = (int) $dateTime->format('j');
        if (!isset($scheduledDays[$dayOfMonth])) {
            $scheduledDays[$dayOfMonth] = [];
        }
        $scheduledDays[$dayOfMonth][] = $event;
    } catch (\Exception $e) {
        // Manejar errores si la fecha es inválida
        // Deberías registrar esto de alguna manera.
    }
}


// --- Lógica del Calendario Dinámico ---

$dateContext = new DateTimeImmutable("$currentYear-$currentMonth-01");
$monthName = $dateContext->format('F');
$yearDisplay = $dateContext->format('Y');

// Días de la semana en español
$daysOfWeek = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

// El primer día del mes (1 = Lunes, 7 = Domingo)
$firstDayOfWeek = (int) $dateContext->format('N'); // 1 (Lun) a 7 (Dom)
$startDay = $firstDayOfWeek == 7 ? 0 : $firstDayOfWeek; // Queremos que el domingo sea el 0

// Número de días en el mes
$daysInMonth = (int) $dateContext->format('t');

// Calcular el mes anterior y el siguiente
$prevMonthDate = $dateContext->modify('-1 month');
$nextMonthDate = $dateContext->modify('+1 month');

$prevMonth = (int) $prevMonthDate->format('m');
$prevYear = (int) $prevMonthDate->format('Y');
$nextMonth = (int) $nextMonthDate->format('m');
$nextYear = (int) $nextMonthDate->format('Y');

// Función simple para traducir el nombre del mes (Idealmente, usaríamos un archivo de traducción o locale)
function translateMonth($monthName) {
    $months = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];
    return $months[$monthName] ?? $monthName;
}

$displayMonthName = translateMonth($monthName);

?>

<div class="schedule-container">
    <div class="calendar-controls">
        <!-- Controles para navegar al mes anterior/siguiente -->
        <a href="?section=schedule&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="control-arrow">&laquo;</a>
        
        <h2 class="current-month-year"><?php echo $displayMonthName . ' ' . $yearDisplay; ?></h2>
        
        <!-- Control para ir al mes siguiente -->
        <a href="?section=schedule&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="control-arrow">&raquo;</a>
    </div>

    <!-- Tabla del Calendario -->
    <table class="calendar-table">
        <thead>
            <tr>
                <?php foreach ($daysOfWeek as $day): ?>
                    <th><?php echo $day; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
            <?php
            $dayCount = 1;
            // Dibuja celdas vacías hasta el primer día del mes
            // El formato 'N' da 1 (Lun) - 7 (Dom). Para empezar en Domingo (0), necesitamos ajustar
            // Usaremos el ajuste donde 0 es Domingo, 1 es Lunes, etc.
            // PHP date('N') devuelve 7 para Domingo. Si queremos que Domingo esté primero (índice 0)
            $dayOfWeekIndex = (int) $dateContext->format('w'); // 0 (Sun) a 6 (Sat)
            
            for ($i = 0; $i < $dayOfWeekIndex; $i++): ?>
                <td class="empty-day"></td>
            <?php endfor;

            // Dibuja los días del mes
            for ($i = 1; $i <= $daysInMonth; $i++):
                // Inicia una nueva fila si es Domingo (0)
                if ((($i - 1 + $dayOfWeekIndex) % 7) == 0 && $i != 1) {
                    echo '</tr><tr>';
                }

                $isToday = ($i == date('j') && $currentMonth == date('m') && $currentYear == date('Y'));
                $hasSchedule = isset($scheduledDays[$i]);
                $dayClass = $isToday ? 'today' : '';
                $dayClass .= $hasSchedule ? ' has-schedule' : '';

                $dateString = sprintf('%s-%s-%s', $currentYear, $currentMonth, $i);
            ?>
                <td class="day-cell <?php echo $dayClass; ?>" data-date="<?php echo $dateString; ?>">
                    <div class="day-number"><?php echo $i; ?></div>
                    <?php if ($hasSchedule): ?>
                        <div class="schedule-indicator">
                            <span class="schedule-count" title="<?php echo count($scheduledDays[$i]); ?> citas"><?php echo count($scheduledDays[$i]); ?></span>
                        </div>
                        <div class="schedule-details">
                            <!-- Popover/tooltip para mostrar detalles del horario -->
                            <?php foreach ($scheduledDays[$i] as $scheduleItem): ?>
                                <p class="schedule-item">
                                    <?php echo (new DateTimeImmutable($scheduleItem['start_time']))->format('H:i'); ?>
                                    - <?php echo $scheduleItem['title']; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </td>
            <?php endfor;

            // Rellena las celdas restantes al final
            $remainingCells = 7 - (($daysInMonth + $dayOfWeekIndex) % 7);
            if ($remainingCells < 7) {
                for ($i = 0; $i < $remainingCells; $i++) {
                    echo '<td class="empty-day"></td>';
                }
            }
            ?>
            </tr>
        </tbody>
    </table>
</div>

<!-- Estilos básicos CSS para el calendario (DEBERÍAS MOVER ESTO A UN ARCHIVO CSS) -->
<style>
.schedule-container {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.calendar-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 0 10px;
}

.current-month-year {
    font-size: 1.8em;
    color: #333;
    font-weight: 600;
}

.control-arrow {
    font-size: 2em;
    text-decoration: none;
    color: #007bff;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.control-arrow:hover {
    background-color: #f0f0f0;
}

.calendar-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.calendar-table th, .calendar-table td {
    border: 1px solid #ddd;
    padding: 0; /* Quitamos padding del td para que el div interno lo maneje */
    text-align: center;
    height: 100px; /* Altura para ver el contenido del día */
    vertical-align: top;
}

.calendar-table th {
    background-color: #f8f8f8;
    color: #555;
    font-weight: 700;
    padding: 10px 0;
}

.day-cell {
    position: relative;
    cursor: default;
    background-color: #fdfdfd;
}

.day-cell:hover {
    background-color: #f4f4f4;
}

.day-number {
    position: absolute;
    top: 5px;
    right: 8px;
    font-size: 1.2em;
    color: #888;
    font-weight: 500;
}

.today {
    background-color: #e6f7ff;
    border: 2px solid #007bff;
}

.today .day-number {
    color: #007bff;
    font-weight: bold;
}

.empty-day {
    background-color: #eee;
    pointer-events: none;
}

/* Estilos de Citas (Schedule) */
.has-schedule {
    background-color: #eafbe1;
}

.schedule-indicator {
    position: absolute;
    bottom: 5px;
    left: 5px;
}

.schedule-count {
    display: inline-block;
    background-color: #28a745;
    color: white;
    font-size: 0.8em;
    padding: 2px 6px;
    border-radius: 12px;
    font-weight: bold;
}

.schedule-details {
    /* Ocultar por defecto, mostrar al pasar el ratón (o con JS para popover) */
    display: none;
    position: absolute;
    z-index: 10;
    bottom: 100%; /* Aparece por encima de la celda */
    left: 0;
    width: 250px;
    background-color: #343a40;
    color: white;
    padding: 10px;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: left;
    transform: translateY(-5px); /* Pequeño desplazamiento */
}

.day-cell:hover .schedule-details {
    /* Para demostración: mostrar al pasar el ratón */
    display: block; 
}

.schedule-item {
    font-size: 0.9em;
    margin: 0 0 5px 0;
    border-bottom: 1px solid #495057;
    padding-bottom: 3px;
}
.schedule-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

</style>