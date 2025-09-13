// Esperar a que cargue el DOM
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
});