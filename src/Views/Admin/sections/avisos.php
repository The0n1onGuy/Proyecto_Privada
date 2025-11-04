<div class="avisos-content">
    <div class="avisos-header">
        <h1>Avisos de la Comunidad</h1>
        <button id="openModalBtn" class="btn-primary">Crear Nuevo Aviso</button>
    </div>

    <div class="avisos-container">
        <?php if (!empty($avisos)): ?>
            <?php foreach ($avisos as $aviso): ?>
                <div class="aviso-card">
                    <div class="aviso-header-card">
                        <span class="usuario">
                            <?= htmlspecialchars($aviso['nombres'] . ' ' . $aviso['apellido_p'] . ' (Casa ' . $aviso['num_casa'] . ')') ?>
                        </span>
                        <span class="fecha"><?= date('d/m/Y', strtotime($aviso['fecha_pub'])) ?></span>
                    </div>
                    <div class="aviso-body">
                        <h4><?= htmlspecialchars($aviso['titulo']) ?></h4>
                        <p><?= nl2br(htmlspecialchars($aviso['contenido'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay avisos disponibles en este momento.</p>
        <?php endif; ?>
    </div>
</div>

<div id="createAvisoModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear Nuevo Aviso</h2>
            <button id="closeModalBtn" class="modal-close">&times;</button>
        </div>
        <form id="createAvisoForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="tipo">Tipo de Aviso</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Aviso">Aviso</option>
                        <option value="Queja">Queja</option>
                        <option value="Sugerencia">Sugerencia</option>
                        <option value="Alerta">Alerta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                <div class="form-group">
                    <label for="contenido">Contenido</label>
                    <textarea id="contenido" name="contenido" rows="5" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelBtn" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Publicar Aviso</button>
            </div>
        </form>
    </div>
</div>