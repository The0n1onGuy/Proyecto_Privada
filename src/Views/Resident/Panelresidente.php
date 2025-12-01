<?php
use App\Core\PopupHelper;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Administrador</title>
  
  <link href="/css/Resident/resident_panel.css" rel="stylesheet">
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
      <div class="avatar">RE</div>
      <div class="profile-text">
        <div style="font-weight:700">Anon</div>
        <div style="font-size:13px; opacity:.75">Residente</div>
      </div>
    </div>

    <nav class="nav-links">
      <a class="nav-link" href="/resident/start" data-view="start" title="Inicio">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8h5z"/></svg>
        <span class="nav-link-text">Inicio</span>
      </a>
      <a class="nav-link" href="/resident/visitas" data-view="visitas" title="Visitas">
        <svg width="24px" height="24px" viewBox="0 0 28.00 28.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" stroke-width="0.00028"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M14 9C12.067 9 10.5 10.567 10.5 12.5C10.5 14.433 12.067 16 14 16C15.933 16 17.5 14.433 17.5 12.5C17.5 10.567 15.933 9 14 9Z" fill="#ffffff"></path> <path d="M10.7001 17C9.84 17 8.904 17.6024 8.87933 18.6719C8.86205 19.421 8.99609 20.5246 9.77391 21.4475C10.5705 22.3927 11.9142 23 14 23C16.0858 23 17.4295 22.3927 18.2261 21.4475C19.0039 20.5246 19.1379 19.421 19.1207 18.6719C19.096 17.6024 18.16 17 17.2999 17H10.7001Z" fill="#ffffff"></path> <path d="M18.8965 4H20.25C21.7688 4 23 5.23122 23 6.75V23.25C23 24.7688 21.7688 26 20.25 26H7.75C6.23122 26 5 24.7688 5 23.25V6.75C5 5.23122 6.23122 4 7.75 4H9.10352C9.42998 2.84575 10.4912 2 11.75 2H16.25C17.5088 2 18.57 2.84575 18.8965 4ZM9.10352 5.5H7.75C7.05964 5.5 6.5 6.05964 6.5 6.75V23.25C6.5 23.9404 7.05964 24.5 7.75 24.5H20.25C20.9404 24.5 21.5 23.9404 21.5 23.25V6.75C21.5 6.05964 20.9404 5.5 20.25 5.5H18.8965C18.57 6.65425 17.5088 7.5 16.25 7.5H11.75C10.4912 7.5 9.42998 6.65425 9.10352 5.5ZM10.5 4.75C10.5 5.44036 11.0596 6 11.75 6H16.25C16.9404 6 17.5 5.44036 17.5 4.75C17.5 4.05964 16.9404 3.5 16.25 3.5H11.75C11.0596 3.5 10.5 4.05964 10.5 4.75Z" fill="#ffffff"></path> </g></svg>
        <span class="nav-link-text">Visitas</span>
      </a>
      <a class="nav-link" href="/resident/residents" data-view="residents" title="Consulta de Residentes">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        <span class="nav-link-text">Residentes</span>
      </a>
      <a class="nav-link" href="/resident/avisos" data-view="avisos" title="Avisos">
        <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.048"></g><g id="SVGRepo_iconCarrier"><path d="M11.99 2.524a.75.75 0 0 1 .812-.495l.763.107a.75.75 0 0 1 .644.7l.042.724a1.5 1.5 0 0 0 .6 1.116l.276.205a5.586 5.586 0 0 1 2.585 4.876l-.142 4.892c-.027.91.316 1.794.95 2.448l.313.321a1.5 1.5 0 0 1 .314 1.608l-.02.05a1.32 1.32 0 0 1-1.408.811l-2.76-.388a3 3 0 0 1-5.94-.834l-5.103-.718a1.32 1.32 0 0 1-1.13-1.168l-.005-.053a1.5 1.5 0 0 1 .745-1.459l.388-.223a3.375 3.375 0 0 0 1.59-2.09l1.211-4.742a5.586 5.586 0 0 1 3.83-3.975l.32-.121a1.5 1.5 0 0 0 .885-.907l.24-.685z" fill="#ffffff"></path></g></svg>
        <span class="nav-link-text">Avisos</span>
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
        <div class="logo-icon">
            <img src="/images/MenuIcon.png" alt="Logo" style="width:50px; height:40px; margin-top: 10px;">
        </div>
        <div class="logo-text">Nexus Access</div>
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
<?php
    // Renderizamos el HTML del popup global
    echo PopupHelper::render('globalPopup'); 
?>
  <!-- CAMBIO 2: Corregido el id a minúsculas para que coincida con el JS -->
  <div id="logoutPopup" class="popup-overlay">
    <div class="popup-content">
        <h2 class="popup-title" style="color: #334155;">Cerrando sesión...</h2>
        <h3 class="popup-text" style="color: #42546cff; font-weight: 300;">Hasta pronto.</h3>        
        <div class="spinner"></div>
    </div>
  </div>

  <!-- SIEMPRE COLOCA ESTE PRIMERO ANTES DEL dataTables, CONFIA!-->
  <script src="/js/Utilities/jquery-3.7.1.min.js"></script> 
  <script src="/js/Utilities/dataTables.js"></script>
  <script src="/js/Resident/resident_panel.js"></script>
  <script src="/js/utilities/popup.js"></script>
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