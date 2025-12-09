<?php
use App\Models\Collaborator\ScheduleModel;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scheduleModel = new ScheduleModel();
$id_privada = $_SESSION['id_privada'] ?? null;

if (!$id_privada) {
    echo '<p class="error-msg">Error: No se ha seleccionado una privada.</p>';
    exit;
}

// Obtener todos los reportes de esta privada
$reports = $scheduleModel->getReportsByPrivada($id_privada);
?>

<div class="reports-wrapper">
    <div class="reports-card">
        <h2>Reportes de Actividades</h2>

        <?php if (empty($reports)): ?>
            <p class="no-reports">No hay reportes levantados aún.</p>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <div class="report-card">
                        <div class="report-header">
                            <h3><?php echo htmlspecialchars($report['nombre_actividad']); ?></h3>
                            <span class="report-date"><?php echo date('d/m/Y H:i', strtotime($report['fecha_reporte'])); ?></span>
                        </div>

                        <div class="report-meta">
                            <p>
                                <strong>Fecha Programada:</strong> 
                                <?php echo date('d/m/Y', strtotime($report['fecha_programada'])); ?>
                            </p>
                            <p>
                                <strong>Reportado por:</strong> 
                                <?php echo htmlspecialchars($report['nombre_usuario_reporta']); ?>
                            </p>
                            <p>
                                <strong>Responsable:</strong> 
                                <?php echo htmlspecialchars($report['nombre_responsable'] ?? 'Sin Asignar'); ?>
                            </p>
                            <p>
                                <strong>Privada:</strong> 
                                <?php echo htmlspecialchars($report['nombre_privada']); ?>
                            </p>
                            <p>
                                <strong>Estado:</strong> 
                                <span class="status-badge <?php echo strtolower($report['nombre_estatus']); ?>">
                                    <?php echo htmlspecialchars($report['nombre_estatus']); ?>
                                </span>
                            </p>
                        </div>

                        <div class="report-content">
                            <h4>Descripción de Ejecución:</h4>
                            <p><?php echo nl2br(htmlspecialchars($report['descripcion_ejecucion'])); ?></p>

                            <?php if ($report['hubo_incidencia']): ?>
                                <div class="incidence-alert">
                                    <h4>⚠️ Incidencia Reportada</h4>
                                    <p><?php echo nl2br(htmlspecialchars($report['descripcion_incidencia'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
