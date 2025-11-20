<script>
    const colaboradoresData = <?php echo $colaboradoresJSON ?? '[]'; ?>;
</script>
<link href="/css/Admin/admin_visitas.css" rel="stylesheet">

<div class="content-header">
    <h1>Gestión de Visitas</h1>
    <p>Añade, edita y administra las visitas.</p>
</div>

<div class="section-card">
    <div class="card-header">
        <h6>Registrados de visitas </h6>
    </div>
    
    <table id="tablaVisitas" class="display" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Visitante</th>
                <th>Tipo de Visita</th>
                <th>Identificación</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Salida</th>
                <th>Residente</th>
                <th>Estatus</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
            </tr>
        </thead>
        <tbody>
<?php if (!empty($visitas)): ?>

    <?php $contador = 1; ?> <!-- Numeración consecutiva -->

    <?php foreach ($visitas as $v): ?>
        <tr>

            <!-- Columna # -->
            <td><?php echo $contador++; ?></td>

            <td><?php echo htmlspecialchars($v['nombre_visitante'] . ' ' . $v['apellido_visitante']); ?></td>
            <td><?php echo htmlspecialchars($v['tipo_visita']); ?></td>
            <td><?php echo htmlspecialchars($v['identificacion']); ?></td>
            <td><?php echo htmlspecialchars($v['fecha_ingreso']); ?></td>

            <td>
                <?php echo $v['fecha_salida'] ? htmlspecialchars($v['fecha_salida']) : '—'; ?>
            </td>

            <td>
                <?php echo htmlspecialchars($v['residente_nombre'] . ' ' . $v['apellido_p'] . ' ' . $v['apellido_m']); ?>
            </td>

            <td>
                <span class="estatus-tag <?php echo strtolower(str_replace(' ', '-', $v['estatus'])); ?>">
                    <?php echo htmlspecialchars($v['estatus']); ?>
                </span>
            </td>

            <td><?php echo htmlspecialchars($v['observaciones'] ?: '—'); ?></td>

            <td>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id_visita" value="<?php echo $v['id_visita']; ?>">

                    <select name="estatus" required class="select-estatus">
                        <option value="">Cambiar</option>
                        <option value="En curso">En curso</option>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>

                    <button type="submit" class="btn-accion">Actualizar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
    </table>
</div>