

<div class="section-card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h6>Listado de Residentes</h6>
        <div class="filter-container" id="customFilterDestination">
            <label for="residentFilter">Mostrar:</label>
            <select id="residentFilter" name="residentFilter">
                <option value="owners" <?php echo ($currentFilter === 'owners') ? 'selected' : ''; ?>>Solo Propietarios</option>
                <option value="all" <?php echo ($currentFilter === 'all') ? 'selected' : ''; ?>>Todos</option>
            </select>
        </div>
    </div>
    
    <table id="tablaResidentes" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Tipo</th>
                <th>Correos</th>
                <th>Teléfonos</th>
                <th>Casa</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($residents)): ?>
                <?php foreach ($residents as $resident): ?>
                    <tr>
                        <td><h1></h1><?php echo $__resident_counter = ($__resident_counter ?? 0) + 1; ?><h1></h1></td>
                        <td><?php echo htmlspecialchars($resident['nombres'] . ' ' . $resident['apellido_p']); ?></td>
                        <td><?php echo $resident['es_propietario'] ? '<span class="badge owner">Propietario</span>' : '<span class="badge resident">Residente</span>'; ?></td>
                        <td>
                            <?php
                            $correos = !empty($resident['correos']) ? explode(', ', $resident['correos']) : [];
                            if (count($correos) === 1) {
                                echo '<a href="mailto:' . htmlspecialchars($correos[0]) . '">' . htmlspecialchars($correos[0]) . '</a>';
                            } elseif (count($correos) > 1) {
                                echo '<select onchange="window.location.href=\'mailto:\'+this.value">';
                                echo '<option>Ver correos...</option>';
                                foreach ($correos as $correo) {
                                    echo '<option value="' . htmlspecialchars($correo) . '">' . htmlspecialchars($correo) . '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $telefonos = !empty($resident['telefonos']) ? explode(', ', $resident['telefonos']) : [];
                            if (count($telefonos) === 1) {
                                echo '<a href="tel:' . htmlspecialchars($telefonos[0]) . '">' . htmlspecialchars($telefonos[0]) . '</a>';
                            } elseif (count($telefonos) > 1) {
                                echo '<select class="contact-select" onchange="if(this.value) window.location.href=\'tel:\'+this.value">';
                                echo '<option value="">Ver teléfonos...</option>';
                                foreach ($telefonos as $telefono) {
                                    echo '<option value="' . htmlspecialchars($telefono) . '">' . htmlspecialchars($telefono) . '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($resident['num_casa']); ?></td>
                        <td><?php echo htmlspecialchars($resident['estatus']); ?></td>
                    </tr>  <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>
    
</div>