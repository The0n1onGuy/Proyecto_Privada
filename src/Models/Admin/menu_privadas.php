<?php
// === menu_privadas.php ===
// Este componente muestra el menú desplegable tipo Netflix para cambiar de privada.
// Se espera que la variable $privadas esté definida antes de incluir este archivo.
// Ejemplo de uso: include 'components/menu_privadas.php';
?>

<div class="private-dropdown">
    <!-- Botón visible con la privada actual -->
    <button id="privateBtn" class="private-btn">
        <img src="/images/villas.png" alt="Privada actual" class="private-avatar">
        <span class="private-name">Seleccionar Privada</span>
        <i class="dropdown-icon">▼</i>
    </button>

    <!-- Menú desplegable de las privadas -->
    <div id="privateMenu" class="private-menu">
        <?php if (!empty($privadas)): ?>
            <?php foreach ($privadas as $privada): ?>
                <form action="/admin/set-private" method="post">
                    <input type="hidden" name="id_privada" value="<?php echo $privada['id_privada']; ?>">
                    <button type="submit" class="private-option-btn">
                        <!-- Imagen dinámica según ID o posición -->
                        <img src="/images/privada_<?php echo $privada['id_privada']; ?>.jpg" 
                             alt="Privada" 
                             class="private-avatar">
                        <span><?php echo htmlspecialchars($privada['nombre']); ?></span>
                    </button>
                </form>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #aaa; text-align:center; margin:10px;">No hay privadas disponibles</p>
        <?php endif; ?>

        <hr class="divider">
        <a href="/admin/configuracion" class="menu-link">Configuración</a>
        <a href="/logout" class="menu-link">Cerrar sesión</a>
    </div>
</div>