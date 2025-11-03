document.addEventListener("DOMContentLoaded", () => {
  // --- Referencias a elementos del DOM ---
  const btnRegresar = document.getElementById("btnRegresar");
  const logoutPopup = document.getElementById("logoutPopup");
  const form = document.querySelector("form");
  const radios = document.querySelectorAll("input[name='id_privada_publica']");
  const welcomePopup = document.getElementById("welcomePopup");

  // ============================================================
  // === BOTÓN REGRESAR: Mostrar mensaje inmediato de cierre ===
  // ============================================================
  if (btnRegresar) {
    btnRegresar.addEventListener("click", (e) => {
      e.preventDefault();

      // Mostrar el popup de "Cerrando sesión..."
      if (logoutPopup) {
        logoutPopup.classList.add("visible");
      }

      // Redirigir tras unos segundos
      setTimeout(() => {
        window.location.href = "/logout"; // o "/login" según tu ruta
      }, 2000);
    });
  }

  // ============================================================
  // === ENVÍO AUTOMÁTICO AL SELECCIONAR UNA PRIVADA ============
  // ============================================================
  if (radios.length > 0 && form) {
    radios.forEach((radio) => {
      radio.addEventListener("change", (e) => {
        e.preventDefault();

        // Mostrar el popup de bienvenida inmediatamente al seleccionar
        if (welcomePopup) {
          welcomePopup.classList.add("visible");
        }

        // Esperar 2 segundos y luego enviar el formulario
        setTimeout(() => {
          form.submit();
        }, 2000);
      });
    });
  }
});