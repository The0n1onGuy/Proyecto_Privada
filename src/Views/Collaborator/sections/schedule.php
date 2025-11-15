<div class="content-wrapper">
    
    <h2>Calendario de Actividades</h2>
    
    <div 
        id="schedule-container" 
        data-calendardata='<?php echo json_encode($calendarData ?? []); ?>'
    >
        <div class="calendar-header">
            <h3>Noviembre 2025</h3> </div>

        <div class="calendar-weekdays">
            <div class="weekday">L</div>
            <div class="weekday">M</div>
            <div class="weekday">M</div>
            <div class="weekday">J</div>
            <div class="weekday">V</div>
            <div class="weekday">S</div>
            <div class="weekday">D</div>
        </div>

        <div id="calendar-grid" class="calendar-grid">
            </div>
    </div>

</div>

<div id="activity-modal-overlay" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 id="modal-title">Actividades del Día</h4>
            <span id="modal-close-btn" class="modal-close">&times;</span>
        </div>
        <div id="modal-content-body" class="modal-body">
            </div>
    </div>
</div>