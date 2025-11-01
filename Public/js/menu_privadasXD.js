// === Control del menú desplegable de privadas ===
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('privateBtn');
    const menu = document.getElementById('privateMenu');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        btn.classList.toggle('active');
        menu.classList.toggle('show'); // Activa la animación
    });

    // Cerrar el menú al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
            btn.classList.remove('active');
            menu.classList.remove('show');
        }
    });
});

// ELIMINAR SI NO SE USA ESTE SCRIPT 29-10-25
