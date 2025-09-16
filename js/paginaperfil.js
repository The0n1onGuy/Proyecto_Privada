function toggleMenu() {
      document.getElementById("sidebar").classList.toggle("active");
    }

function cerrarSesion() {
      // Aquí se simula el cierre de sesión
      alert("Sesión cerrada correctamente.");
      window.location.href = "../index.php"; // Redirección al login
    }