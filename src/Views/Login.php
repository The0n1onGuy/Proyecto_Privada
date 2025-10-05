<?php
// El controlador ya se ha encargado de definir la variable $error_message.
// Esta vista solo se preocupa por mostrar los datos.
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privada Residencial - Inicio</title>
  <!-- Asegúrate de que la ruta a tu CSS sea correcta -->
  <link href="/css/login.css" rel="stylesheet">
</head>
<!-- 
  Pasamos el mensaje de error de PHP a un atributo 'data-error-message'.
  Nuestro JS lo leerá para mostrar el popup.
-->
<body data-error-message="<?php echo htmlspecialchars($error_message ?? '', ENT_QUOTES, 'UTF-8'); ?>">
  
  <!-- Fondo con efecto blur -->
  <div class="background-container"></div>
  
  <!-- Contenedor para centrar el formulario -->
  <main class="login-container-wrapper">
    <div class="login-form-container">
        
        <!-- NUEVO: Cabecera con logo y título dentro del formulario -->
        <div class="form-header">
            <div class="logo-placeholder"></div>
            <h1 class="main-title">Nexus Access</h1>
        </div>

        <h3 class="subtitle">Inicio de Sesión</h3>
        <form action="/login" method="post">
            <label for="usuario">Usuario</label>        
            <input name="usuario" id="campoUsuario" type="text" required>
                
            <label for="contrasenia">Contraseña</label>
            <input name="contrasenia" id="campoContra" type="password" required>
            
            <button type="submit" class="btn">
                Iniciar Sesión
            </button>
        </form>
    </div>
  </main>

  <!-- Popups para mostrar mensajes-->
  <div id="errorPopup" class="popup-overlay">
      <div class="popup-content">
          <h2 class="popup-title">Error de Autenticación</h2>
          <p id="popupMessage" class="popup-message"></p>
          <button class="popup-close-btn">Entendido</button>
      </div>
  </div>

  <div id="loadingPopup" class="popup-overlay">
    <div class="popup-content">
        <!-- Usamos un color de texto neutro en lugar del rojo de error -->
        <h2 class="popup-title" style="color: #334155;">Verificando Credenciales...</h2>
        <div class="spinner"></div>
    </div>
</div>

  <!-- El script se enlaza al final para un mejor rendimiento -->
  <script src="/js/login.js" defer></script>
</body>
</html>

