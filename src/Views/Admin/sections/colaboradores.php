<div class="content-header">
    <h1>Gestión de Colaboracasfdores</h1>
    <p>Añade, edita y administra al personal.</p>
</div>

<div class="section-card">
    <div class="card-header">
        <h6>Colaboradores Registrados</h6>
        <button id="addCollaboratorBtn" class="btn-primary">
            <i class="fas fa-plus"></i> Añadir Nuevo
        </button>
    </div>
    
    <table id="tablaColaboradores" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo Electrónico</th>
                <th>Número telefónico</th>
                <th>Privada Asignada</th>
                <th>Estatus</th>
                <th>Rol</th>
                <th>Acciones</th> 
            </tr>
        </thead>
        <?php
            if (!empty($colaboradores)) {
                foreach ($colaboradores as $item) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($item['id_usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($item['nombres'] . ' ' . $item['apellido_p'] . ' ' . $item['apellido_m']) . "</td>";
                    echo "<td>" . htmlspecialchars($item['correo']) . "</td>";
                    echo "<td>" . htmlspecialchars($item['telefono']) . "</td>";
                    echo "<td>" . htmlspecialchars($item['privada_nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($item['rol']) . "</td>";
                    echo "<td>" . htmlspecialchars($item['estatus']) . "</td>";
                    echo '<td>
                            <button class="btn-icon btn-edit" title="Editar">✏️</button>
                            <button class="btn-icon btn-delete" title="Eliminar">🗑️</button>
                          </td>';
                    echo "</tr>";
                }
            }
            ?>
    </table>
</div>

<div id="collaboratorModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="modalTitle">Añadir Nuevo Colaborador</h5>
            <button id="closeModalBtn" class="modal-close">&times;</button>
        </div>
        <form id="collaboratorForm">
            <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol" required>
                    <option value="" disabled selected>Seleccionar rol...</option>
                    <?php foreach ($roles as $item): ?>
                        <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="privada">Privada Asignada</label>
                <select id="privada" name="privada" required>
                    <option value=""disabled selected>Seleccionar privada...</option>
                    <?php if (!empty($Pcolaboradores)): ?>
                        <?php foreach ($Pcolaboradores as $item): ?>
                            <option value="<?= htmlspecialchars($item['nombre']) ?>">
                                <?= htmlspecialchars($item['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="estatus">Estatus</label>
                <select id="estatus" name="estatus" required>
                    <?php foreach ($estatus as $item): ?>
                        <option value="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            </form>
    </div>
</div>