// public/js/Resident/avisos.js
(function() {
    // --- ELEMENTOS DEL DOM ---
    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const modal = document.getElementById("createAvisoModal");
    const form = document.getElementById("createAvisoForm");

    // --- FUNCIONES ---
    const showModal = () => {
        if (modal) modal.classList.add("visible");
    };

    const hideModal = () => {
        if (modal) modal.classList.remove("visible");
    };

    // --- MANEJADORES DE EVENTOS ---

    // Botón para abrir el modal
    if (openModalBtn) {
        openModalBtn.addEventListener("click", showModal);
    }

    // Botones para cerrar el modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener("click", hideModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener("click", hideModal);
    }

    // Cerrar el modal al hacer clic fuera de él
    if (modal) {
        modal.addEventListener("click", (event) => {
            if (event.target === modal) {
                hideModal();
            }
        });
    }

    // Manejo del envío del formulario
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.textContent = 'Publicando...';
            submitButton.disabled = true;

            fetch('/resident/avisos/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) { // Aquí se implementan las llamadas para los popups globales
                    showGlobalPopup('Aviso Creado', data.message, 'Entendido', 'success'); 
                    // En esta linea arriba, statement: "Título, mensaje, texto botón, tipo"
                    hideModal();
                    form.reset();
                    // Usamos la función global para recargar la sección y ver el nuevo aviso
                    if (window.loadSection) {
                        window.loadSection('avisos');
                    }
                } else { // Se incluye el popup para el error
                    showGlobalPopup('Error al Crear Aviso', data.message, 'Entendido', 'error');
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error); // Abajo también se incluye el popup de error genérico
                showGlobalPopup('Error de Red', 'Ocurrió un error al comunicarse con el servidor. Por favor, inténtalo de nuevo más tarde.', 'Entendido', 'error');
            })
            .finally(() => {
                submitButton.textContent = 'Publicar Aviso';
                submitButton.disabled = false;
            });
        });
    }

    // --- ANIMACIONES ---
    const avisos = document.querySelectorAll(".aviso-card");
    if (avisos) {
        avisos.forEach((card, i) => {
            card.style.animationDelay = `${i * 100}ms`;
        });
    }

})(); // Envolvemos todo en una IIFE para evitar conflictos en el scope global. MERGE VER.