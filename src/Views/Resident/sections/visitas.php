<title>Gestión de Visitas</title>

<div class="visitas-container">
    <div class="header-visitas">
        <span>Visitas</span>
        <button id="btnAgregarVisita" class="btn-agregar">+</button>
    </div>

    <div id="formNuevaVisita" class="form-visita">
        <h3>Agregar Nueva Visita</h3>
        <form method="POST">
            <input type="text" name="nombre_visitante" placeholder="Nombre(s)" required>
            <input type="text" name="apellido_visitante" placeholder="Apellido(s)" required>
            
            <select name="tipo_visita" required>
                <option value="" disabled selected>Selecciona el tipo de visita</option>
                <option value="Familiar">Familiar</option>
                <option value="Amigo">Amigo</option>
                <option value="Otro">Otro</option>
            </select>

            <input type="text" name="observaciones" placeholder="Observaciones (opcional)">

            <input type="hidden" name="id_residente" value="<?= htmlspecialchars($_SESSION['id_info']); ?>">
            
            <input type="hidden" name="estatus" value="Activo">
            
            <button type="submit" class="btn-guardar">Guardar</button>
        </form>
    </div>

    <div class="lista-visitas">
        <?php foreach ($visitas as $v): ?>
            <div class="visita-card">
                <div class="visita-header">
                    <div class="visita-info">
                        <h3><?php echo htmlspecialchars($v['nombre_visitante'] . ' ' . $v['apellido_visitante']); ?></h3>
                        <p><?= htmlspecialchars($v['tipo_visita']); ?></p>
                    </div>
                    <div class="visita-status">
                        <span class="estado <?= strtolower($v['estatus']); ?>">
                            <?= htmlspecialchars(strtolower($v['estatus'])); ?>
                        </span>
                        <button class="toggle-detalle">▼</button>
                    </div>
                </div>

                <div class="visita-detalle">
                    <p><strong>Observaciones:</strong> <?= htmlspecialchars($v['observaciones']); ?></p>
                    
                    <form method="POST" class="form-estatus">
                        <input type="hidden" name="id_visita" value="<?= $v['id_visita']; ?>">
                        <select name="estatus">
                            <option value="">Cambiar estatus</option>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                        <button type="submit">Actualizar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>