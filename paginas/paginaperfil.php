<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modificar Perfil de Usuario</title>
  <link href="../estilos/paginaperfil.css" rel="stylesheet">
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

  <!-- Contenido principal -->
  <div class="main">
    <div class="card">
      <h5>✏️ Modificar Perfil de Usuario</h5>
      <form>
        <label>Nombre de usuario</label>
        <input type="text" class="form-control" value="admin">

        <label>Nombre</label>
        <input type="text" class="form-control" value="Sistemas Webs">

        <label>Email</label>
        <input type="email" class="form-control" value="info@sist.com">

        <label>Teléfono</label>
        <input type="text" class="form-control" value="7025">

        <label>Foto</label>
        <input type="file" class="form-control">

        <div class="profile-img">👤</div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary">Cancelar</button>
      </form>
    </div>
  </div>

  <!-- Script menú -->
  <script src="../js/paginaperfil.js"></script> </script>

</body>
</html>


