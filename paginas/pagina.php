<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PAGINA PRINCIPAL</title>
  <link href="../estilos/pagina.css" rel="stylesheet">
</head>
<body>

  <!-- Barra superior -->
  <div class="topbar">
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
    <div class="search-box">
      <input type="text" placeholder="Buscar...">
    </div>
  </div>

  <!-- Menú lateral -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <button onclick="toggleMenu()">☰</button>
      <h2>Menú</h2>
    </div>
    <div herf="#">👤</div>
    <a href="#">JUANITOPRO</a>
    <a href="pagina.php">Home</a>
    <a href="paginaperfil.php">Perfil</a>
    <a href="#">Contactanos!</a>
    
    <!-- Botón Cerrar Sesión -->
    <a href="#" class="logout-btn" onclick="cerrarSesion()">Cerrar Sesión</a>
  </div>

 
  <div class="main">
    <h1>Bienvinido:v</h1>
    <p>.___.</p>
  </div>

 <script src="../js/pagina.js"> </script>

</body>
</html>