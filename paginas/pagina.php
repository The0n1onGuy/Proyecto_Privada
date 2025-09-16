<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PÁGINA PRINCIPAL</title>
  <link rel="stylesheet" href="../estilos/pagina.css">
</head>
<body>
  <!-- Barra superior -->
  <div class="topbar">
    <button class="menu-btn" onclick="toggleMenu()">☰</button>

<<<<<<< HEAD
<body class ="center">
  <div class="container">

      <h1 class="title">Contactanos</h1><br>
          
          <div class="contenedor">
           
             <button id="butn" type="submit" class="btn" > <a href="<?php echo $Starter-> getwhatsApplink("Gay");?>" target="_blank"><img class="mi-imagen" src="../estilos/WhatsApp.jpg" alt="Una ilustración colorida"></a></button></br>
          </div>

            

=======
    <!-- Botones para mostrar tablas, integrados en la barra -->
    <div class="topbar-buttons">
      <button onclick="showTable('tabla1')">Tabla 1</button>
      <button onclick="showTable('tabla2')">Tabla 2</button>
      <button onclick="showTable('tabla3')">Tabla 3</button>
>>>>>>> Pruebas
    </div>

    <div class="search-box">
      <input type="text" placeholder="Buscar...">
    </div>
  </div>

  <!-- Overlay para menú en móviles -->
  <div id="overlay" class="overlay" onclick="toggleMenu()"></div>

  <!-- Menú lateral -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <button onclick="toggleMenu()">×</button>
      <h2>Menú</h2>
    </div>
    <div>👤</div>
    <a href="#">JUANITOPRO</a>
    <a href="pagina.php">Inicio</a>
    <a href="paginaperfil.php">Perfil</a>
    <a href="#">¡Contáctanos!</a>
    <a href="#" class="logout-btn" onclick="cerrarSesion()">Cerrar Sesión</a>
  </div>

  <!-- Contenido principal -->
  <div class="main">
    <table id="tabla1" class="data-table">
      <thead><tr><th>ID</th><th>Nombre</th><th>Edad</th></tr></thead>
      <tbody>
        <tr><td>1</td><td>Juan</td><td>25</td></tr>
        <tr><td>2</td><td>Maria</td><td>30</td></tr>
      </tbody>
    </table>

    <table id="tabla2" class="data-table">
      <thead><tr><th>ID</th><th>Producto</th><th>Precio</th></tr></thead>
      <tbody>
        <tr><td>101</td><td>Computadora</td><td>$1200</td></tr>
        <tr><td>102</td><td>Mouse</td><td>$20</td></tr>
      </tbody>
    </table>

    <table id="tabla3" class="data-table">
      <thead><tr><th>ID</th><th>Ciudad</th><th>País</th></tr></thead>
      <tbody>
        <tr><td>1</td><td>Ciudad de México</td><td>México</td></tr>
        <tr><td>2</td><td>Buenos Aires</td><td>Argentina</td></tr>
      </tbody>
    </table>
  </div>

  <script src="../js/pagina.js"></script>
</body>
</html>
