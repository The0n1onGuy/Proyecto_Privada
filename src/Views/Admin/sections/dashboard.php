
<div class = "dashboard-columns">
    <div class="dashboard-column">
        <!-- Este es un segmento de HTML que se carga dentro del Panel.php -->
        <div class="section-card">
            <h2 class="section-title">Resumen de Ingresos</h2>
            <div class="chart-container">
                <canvas id="myChart"></canvas>
            </div>
        </div>

        <div class="section-card">
            <h2 class="section-title">Acciones Rápidas</h2>
            <!-- Aquí podrías poner botones o enlaces a otras secciones -->
        </div>
    </div>
    
    <div class="dashboard-column">
        <div class="section-card">
            <h2 class="section-title">Reportes y Avisos</h2>
            <table id="tablaReportes" class="display compact" style="width:100%">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Título</th>
                        <th>Privada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // This PHP loop will populate the table.
                    // We will create the $noticias variable in the next step.
                    if (!empty($reportes)) {
                        foreach ($reportes as $item) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($item['fecha']) . "</td>";
                            echo "<td>" . htmlspecialchars($item['titulo']) . "</td>";
                            echo "<td>" . htmlspecialchars($item['id_privada']) . "</td>";
                            echo "</tr>";
                        }
                    }?>
                </tbody>
            </table>
        </div>
        <div class="section-card">
            <table id="tablaAvisos" class="display compact" style="width:100%">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Mensaje</th>
                        <th>Privada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // This PHP loop will populate the table.
                    // We will create the $noticias variable in the next step.
                    if (!empty($avisos)) {
                        foreach ($avisos as $item) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($item['fecha']) . "</td>";
                            echo "<td>" . htmlspecialchars($item['titulo']) . "</td>";
                            echo "<td>" . htmlspecialchars($item['id_privada']) . "</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- ------ [INICIO] CÓDIGO NUEVO ------ -->
<!-- Este script pasa los datos calculados en PHP a una variable de JavaScript -->
<script>
    // La variable $paymentStatsJSON fue creada en el AdminController
    const paymentDataFromPHP = <?php echo $paymentStatsJSON ?? '[]'; ?>;
</script>

<!-- ------ [FIN] CÓDIGO NUEVO ------ -->
</div>
