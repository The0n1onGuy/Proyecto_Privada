
/* -------------- Codigo para el menu lateral de moviles ---------------- */

// Sidebar móvil

const mobileMenuBtn = document.getElementById('mobileMenuBtn');

// Crear overlay dinámicamente
let overlay = document.createElement('div');
overlay.id = 'sidebarOverlay';
document.body.appendChild(overlay);

mobileMenuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
});

// Cerrar al hacer clic en overlay
overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
});

// Cerrar al seleccionar un enlace (solo en móviles)
const navLinks = document.querySelectorAll('#sidebar .nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
});


// 🔹 Espera a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {

    // 🔹 Obtiene referencias al botón y al menú
    const privateBtn = document.getElementById("privateBtn");
    const privateMenu = document.getElementById("privateMenu");

    // 🔹 Alterna la visibilidad del menú al hacer clic en el botón
    privateBtn.addEventListener("click", function(e) {
        e.stopPropagation(); // Evita que el clic cierre el menú inmediatamente
        privateMenu.style.display = privateMenu.style.display === "block" ? "none" : "block";
    });

    // 🔹 Cierra el menú si el usuario hace clic fuera de él
    document.addEventListener("click", function() {
        privateMenu.style.display = "none";
    });
});
