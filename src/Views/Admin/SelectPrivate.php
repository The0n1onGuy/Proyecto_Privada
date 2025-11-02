<?php
    // --- CAMBIO ---
    // Importamos la nueva clase de Seguridad al inicio del archivo
    use App\Core\SessionVerifier;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Privada</title>

    <!-- 🔹 Enlace al archivo CSS donde se define el estilo visual del diseño -->
    <link rel="stylesheet" href="/css/Utilities/selec_privadas.css">
</head>
<body>
    <!-- 🔹 Encabezado superior del sitio -->
    <div class="header">
        <!-- Logo del sistema o icono del menú -->
        <img src="/images/MenuIcon.png" alt="Logo" class="logo">
        <!-- Nombre o título de la aplicación -->
        <span class="titulo-header">Nexus Access</span>
    </div>

    <!-- 🔹 Contenedor principal que agrupa todo el contenido -->
    <div class="main-container">
        <!-- Título principal de la página -->
        <h1 class="titulo-principal">¿Qué privada deseas administrar?</h1>

        <!-- 🔹 Formulario que envía la selección del usuario al servidor -->
        <form action="/admin/set-private" method="post" class="form-privadas">
            
            <!-- Contenedor donde se generan las tarjetas de privadas desde la base de datos -->
            <div class="privadas-container">

                <!-- 🔹 Bucle que recorre todas las privadas obtenidas -->
                <?php foreach ($privadas as $privada): ?>

                    <?php
                        /*
                        ==========================================================
                        BLOQUE DE CONFIGURACIÓN DE IMAGEN POR PRIVADA
                        ==========================================================
                        - Se crea un nombre de archivo basado en el nombre de la privada.
                        - Ejemplo: "Villas del Sol" → "villas_del_sol.jpg"
                        - Se busca ese archivo en la carpeta /images/privadas/
                        - Si no existe, se usa una imagen por defecto.
                        ==========================================================
                        */

                        // Convierte el nombre de la privada a minúsculas y reemplaza espacios por guiones bajos
                        $nombreArchivo = str_replace(' ', '_', strtolower($privada['nombre'])) . '.jpg';
                        
                        // Define la ruta base de la imagen esperada
                        $rutaImagen = "/images/privadas/" . $nombreArchivo;

                        // Verifica si el archivo existe en el servidor
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $rutaImagen)) {
                            // Si no existe, se asigna una imagen genérica o de respaldo
                            $rutaImagen = "/images/default_privada.png";
                        }
                    ?>

                    <!-- 🔹 Input tipo "radio" oculto, para que el usuario seleccione una privada -->
                    <input type="radio" 
                           id="privada-<?php echo $privada['id_privada']; ?>" 
                           name="id_privada" 
                           value="<?php echo SessionVerifier::encryptId($privada['id_privada']); ?>" 
                           hidden>

                    <!-- 🔹 Tarjeta visual que representa cada privada -->
                    <label for="privada-<?php echo $privada['id_privada']; ?>" class="privada-card">

                        <!-- Imagen dinámica que se carga según el nombre de la privada -->
                        <img src="<?php echo htmlspecialchars($rutaImagen); ?>" 
                             alt="Imagen de <?php echo htmlspecialchars($privada['nombre']); ?>" 
                             class="privada-img">

                        <!-- Nombre visible de la privada -->
                        <span class="privada-nombre">
                            <?php echo htmlspecialchars($privada['nombre']); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- 🔹 Botón para regresar (se conserva) -->
            <button type="button" class="btn-regresar" id="btnRegresar">Cerrar Sesión</button>
            
            <!-- 🔸 Botón “Continuar” eliminado como solicitaste -->
        </form>

    <!-- 🔹 Popup de cierre de sesión -->
    <div id="logoutPopup" class="popup-overlay">
        <div class="popup-content">
            <h2 class="popup-title" style="color: #334155;">Cerrando sesión...</h2>
            <h3 class="popup-text" style="color: #42546cff; font-weight: 300;">Hasta pronto.</h3>        
            <div class="spinner"></div>
        </div>
    </div>

   <!-- 🔹 Popup de bienvenida -->
<div id="welcomePopup" class="popup-overlay">
    <div class="popup-content">
        <h2 class="popup-title" style="color: #334155;">¡Bienvenido de nuevo!</h2>
        <h3 class="popup-text" style="color: #42546cff; font-weight: 300;">
            Nos alegra verte administror.
        </h3>
        <div class="spinner"></div>
    </div>
</div>

    <!-- 🔹 Script JavaScript para validar la selección -->
    <script src="/js/Admin/selec_priv.js"></script>
</body>
</html>