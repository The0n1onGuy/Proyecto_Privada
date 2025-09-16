document.addEventListener("DOMContentLoaded", function() {

  // Menú lateral
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");

  window.toggleMenu = function() {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
  }

  window.cerrarSesion = function() {
    alert("Sesión cerrada correctamente.");
    window.location.href = "../index.php";
  }

  // Mostrar tablas
  window.showTable = function(idTabla) {
    const tablas = document.querySelectorAll(".data-table");
    tablas.forEach(tabla => tabla.style.display = "none");
    const tablaSeleccionada = document.getElementById(idTabla);
    if (tablaSeleccionada) tablaSeleccionada.style.display = "table";
  }

  // Mostrar la primera tabla por defecto
  showTable('tabla1');

});


/* Esperar a que cargue el DOM
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("butn");

    if (btn) {
        btn.addEventListener("click", function() {
            // Leer el enlace generado en PHP desde data-url
            const url = this.getAttribute("data-url");

            if (url) {
                // Abrir en nueva pestaña (como target="_blank")
                window.open(url, "_blank");
            } else {
                console.error("No se encontró la URL en el botón.");
            }
        });
    }
});*/

 