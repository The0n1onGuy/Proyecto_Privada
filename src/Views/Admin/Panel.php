<?php
use App\Core\PopupHelper;
?>
<?php
// --- INICIO DE LÓGICA DE DATOS DE PRIVADA ---
// Definimos variables por defecto
$current_privada_name = "Seleccionar Privada";
$current_privada_img = "/images/default_privada.png";

// Verificamos si la variable $privada_actual fue cargada por el controlador
if (isset($privada_actual) && $privada_actual) {
    $current_privada_name = htmlspecialchars($privada_actual['nombre']);
    
    // Construimos la ruta de la imagen
    $nombre_archivo_img = strtolower(str_replace(' ', '_', $privada_actual['nombre'])) . '.jpg';
    $ruta_imagen_privada = "/images/privadas/" . $nombre_archivo_img;
    
    // Usamos la imagen de la privada, pero con un fallback a la default
    $current_privada_img = $ruta_imagen_privada;
}
// --- FIN DE LÓGICA DE DATOS DE PRIVADA ---
?>

<!doctype html>
<html lang="es">
<head>


  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Administrador</title>
  
  <link href="/css/Admin/admin_panel.css" rel="stylesheet">
  <link href="/css/Admin/admin_movil.css" rel="stylesheet">
  <!-- Estilo para manejar la distribucion de elementos-->
  <link href="/css/Admin/admin_sectionLayout.css" rel="stylesheet">
  <link href="/css/utilities/popup.css" rel="stylesheet">

    <?php
    // Carga los estilos específicos que el controlador haya definido para la sección
    if (isset($assets['styles'])) {
        foreach ($assets['styles'] as $style) {
            echo "<link href=\"$style\" rel=\"stylesheet\">";
        }
    }
  ?>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css" />
</head>
<body>
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="menu-toggle" id="menu-toggle"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="menu-alt" class="icon glyph" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M21,19H3a1,1,0,0,1,0-2H21a1,1,0,0,1,0,2Z" style="fill:#ffffff"></path><path d="M21,13H3a1,1,0,0,1,0-2H21a1,1,0,0,1,0,2Z" style="fill:#ffffff"></path><path d="M21,7H3A1,1,0,0,1,3,5H21a1,1,0,0,1,0,2Z" style="fill:#ffffff"></path></g></svg></button>
    </div>

    <div class="profile-sm">
      <div class="avatar">AD</div>
      <div class="profile-text">
        <div style="font-weight:700">Admin</div>
        <div style="font-size:13px; opacity:.75">Administrador</div>
      </div>
    </div>

    <nav class="nav-links">
      <a class="nav-link" href="/admin/dashboard" data-view="dashboard" title="Panel Principal">
        <svg fill="#ffffff" width="24px" height="24px" viewBox="0 0 24.00 24.00" xmlns="http://www.w3.org/2000/svg" id="dashboard-alt" class="icon glyph" stroke="#ffffff" stroke-width="0"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M14,10V22H4a2,2,0,0,1-2-2V10Z"></path><path d="M22,10V20a2,2,0,0,1-2,2H16V10Z"></path><path d="M22,4V8H2V4A2,2,0,0,1,4,2H20A2,2,0,0,1,22,4Z"></path></g></svg>
        <span class="nav-link-text">Dashboard</span>
      </a>
      
      
      <a class="nav-link" href="/admin/residents" data-view="residents" title="Gestión de Residentes">
        <svg width="24px" height="24px" viewBox="-1.6 -1.6 19.20 19.20" xmlns="http://www.w3.org/2000/svg" fill="#ffffff" class="bi bi-people-fill"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path> <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"></path> <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"></path> </g></svg>
        <span class="nav-link-text">Residentes</span>
      </a>
      <a class="nav-link" href="/admin/servicios" data-view="servicios" title="Servicios">
        <svg width="24" height="24" viewBox="0 0 100 100" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M71.83,20H28.3A8.11,8.11,0,0,0,20,27.81V72.19A8.08,8.08,0,0,0,28.16,80h11c1.1-4.54,6.46-7.17,12.14-9.49,4.24-1.8,4.89-3.34,4.89-5.14s-1.29-3.47-2.7-4.76a11.3,11.3,0,0,1-3.86-9c0-6.69,4.37-12.6,12-12.6s12,5.79,12,12.6a12.13,12.13,0,0,1-3.86,9c-1.41,1.41-2.7,3-2.7,4.76s.51,3.34,4.89,5.14a41.53,41.53,0,0,1,7.78,3.91A7.28,7.28,0,0,0,80,72.19V27.81C80.14,23.56,76.42,20,71.83,20ZM47.61,33.35a2.85,2.85,0,0,1-2.76,2.75H30.78A2.85,2.85,0,0,1,28,33.35V30.6a2.85,2.85,0,0,1,2.76-2.75H44.85a2.85,2.85,0,0,1,2.76,2.75Z" fill-rule="evenodd"/>
        </svg>
        <span class="nav-link-text">Servicios</span>
      </a>
      <a class="nav-link" href="/admin/colaboradores" data-view="colaboradores" title="Colaboradores">
        <svg width="24" height="24" viewBox="0 0 100 100" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M44,63.3c0-3.4,1.1-7.2,2.9-10.2c2.1-3.7,4.5-5.2,6.4-8c3.1-4.6,3.7-11.2,1.7-16.2c-2-5.1-6.7-8.1-12.2-8  s-10,3.5-11.7,8.6c-2,5.6-1.1,12.4,3.4,16.6c1.9,1.7,3.6,4.5,2.6,7.1c-0.9,2.5-3.9,3.6-6,4.6c-4.9,2.1-10.7,5.1-11.7,10.9  c-1,4.7,2.2,9.6,7.4,9.6h21.2c1,0,1.6-1.2,1-2C45.8,72.7,44,68.1,44,63.3z M64,48.3c-8.2,0-15,6.7-15,15s6.7,15,15,15s15-6.7,15-15  S72.3,48.3,64,48.3z M66.6,64.7c-0.4,0-0.9-0.1-1.2-0.2l-5.7,5.7c-0.4,0.4-0.9,0.5-1.2,0.5c-0.5,0-0.9-0.1-1.2-0.5  c-0.6-0.6-0.6-1.7,0-2.5l5.7-5.7c-0.1-0.4-0.2-0.7-0.2-1.2c-0.2-2.6,1.9-5,4.5-5c0.4,0,0.9,0.1,1.2,0.2c0.2,0,0.2,0.2,0.1,0.4  L66,58.9c-0.2,0.1-0.2,0.5,0,0.6l1.7,1.7c0.2,0.2,0.5,0.2,0.7,0l2.5-2.5c0.1-0.1,0.4-0.1,0.4,0.1c0.1,0.4,0.2,0.9,0.2,1.2  C71.6,62.8,69.4,64.9,66.6,64.7z"/>
        </svg>
        <span class="nav-link-text">Colaboradores</span>
      </a>
      <a class="nav-link" href="/admin/avisos" data-view="avisos" title="Avisos">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" d="M9.82664 2.22902C10.7938 0.590326 13.2063 0.590325 14.1735 2.22902L23.6599 18.3024C24.6578 19.9933 23.3638 22 21.4865 22H2.51362C0.63634 22 -0.657696 19.9933 0.340215 18.3024L9.82664 2.22902ZM10.0586 7.05547C10.0268 6.48227 10.483 6 11.0571 6H12.9429C13.517 6 13.9732 6.48227 13.9414 7.05547L13.5525 14.0555C13.523 14.5854 13.0847 15 12.554 15H11.446C10.9153 15 10.477 14.5854 10.4475 14.0555L10.0586 7.05547ZM14 18C14 19.1046 13.1046 20 12 20C10.8954 20 10 19.1046 10 18C10 16.8954 10.8954 16 12 16C13.1046 16 14 16.8954 14 18Z" />
        </svg>
        <span class="nav-link-text">Avisos</span>
      </a>
      <a class="nav-link" href="/admin/visitas" data-view="visitas" title="visitas">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20h4V4h-4v16zm-6 0h4v-8H4v8zM16 9v11h4V9h-4z"/></svg>
        <span class="nav-link-text">Visitas</span>
      </a>
      <a class="nav-link" href="/admin/configs" data-view="configs" title="Configuración">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
        <span class="nav-link-text">Configuración</span>
      </a>
      <!-- CAMBIO 1: Añadido el id="logout-button" al enlace -->
      <a id="logout-button" class="nav-link" href="/logout" title="Cerrar Sesión">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
        <span class="nav-link-text">Cerrar Sesión</span>
      </a>
    </nav>
  </aside>

  <header class="topbar" id="topbar">
    <div class="topbar-start">
    <button id="mobileMenuBtn" class="mobile-menu-btn">&#9776;</button> 
    <div class="logo-icon">
        <img src="/images/MenuIcon.png" alt="Logo" style="width:50px; height:40px; margin-top: 10px;">
    </div>
    <div class="logo-text">Nexus Access</div>
<div class="private-dropdown">
  <!-- <button class="private-btn" id="privateBtn">
    <img src="/images/default_privada.png" alt="" class="private-avatar" id="privateAvatar">
    <span class="private-name" id="privateName">Seleccionar Privada</span>
    <svg class="dropdown-icon" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" fill="white"/></svg>
  </button> -->
  <button class="private-btn" id="privateBtn">
    <img 
        src="<?php echo $current_privada_img; ?>" 
        alt="Logo Privada" 
        class="private-avatar" 
        id="privateAvatar"
        onerror="this.src='/images/default_privada.png'">
    <span class="private-name" id="privateName">
        <?php echo $current_privada_name; ?>
    </span>
  </button>
  <div class="private-menu" id="privateMenu"></div>
</div>
</div>

</div>

    <div class="topbar-end">
        </div>
  </header>

    <main class="content-wrap" id="content-wrap">
    <?php
        // Incluye la vista parcial (el "segmento") que el controlador especificó
        if (isset($view_to_load) && file_exists(__DIR__ . '/sections/' . $view_to_load)) {
            // La función extract() convierte las claves del array $data en variables
            // ej. $data['users'] se convierte en la variable $users
            extract($data ?? []); 
            include __DIR__ . '/sections/' . $view_to_load;
        } else {
            // Muestra un mensaje si la vista parcial no se encuentra
            echo "<div class='section-card'><h2>Sección no encontrada</h2></div>";
        }
    ?>
  </main>

  <!-- CAMBIO 2: Corregido el id a minúsculas para que coincida con el JS -->
  <div id="logoutPopup" class="popup-overlay">
    <div class="popup-content">
        <h2 class="popup-title" style="color: #334155;">Cerrando sesión...</h2>
        <h3 class="popup-text" style="color: #42546cff; font-weight: 300;">Hasta pronto.</h3>        
        <div class="spinner"></div>
    </div>
  </div>
  <div id="resultPopup" class="popup-overlay">
    <div class="popup-content">
        <h2 id="resultPopupTitle" class="popup-title"></h2>
        <p id="resultPopupMessage" class="popup-message"></p>
        <button id="resultPopupCloseBtn" class="popup-close-btn">Entendido</button>
    </div>
  </div>
  <!-- SIEMPRE COLOCA ESTE PRIMERO ANTES DEL dataTables, CONFIA!-->
  <script src="/js/Utilities/jquery-3.7.1.min.js"></script> 
  <script src="/js/Utilities/dataTables.js"></script>
  <script src="/js/Admin/admin_panel.js"></script>
  <script src="/js/Admin/admin_movil.js"></script>
  <script src="/js/Utilities/popup.js"></script>

    <?php
    // Carga los scripts específicos que el controlador haya definido
    if (isset($assets['scripts'])) {
        foreach ($assets['scripts'] as $script) {
            echo "<script src=\"$script\"></script>";
        }
    }
    ?>
</body>
</html>


<!-- THE CODE END HERE   REFERENCE -->

