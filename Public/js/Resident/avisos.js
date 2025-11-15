(function() {
    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const modal = document.getElementById("createAvisoModal");
    const form = document.getElementById("createAvisoForm");
    const misAvisosBtn = document.getElementById("misAvisosBtn");
    const avisosContainer = document.getElementById("avisosContainer");

    let mostrandoMisAvisos = false;


// Buscar el contenedor principal que tiene el ID del usuario
const avisosContent = document.querySelector(".avisos-content");
// Leer el ID del usuario desde el atributo data- y convertirlo a número
const usuarioActual = parseInt(avisosContent?.getAttribute("data-session-user-id") || 0);

   
    const showModal = () => modal?.classList.add("visible");
    const hideModal = () => modal?.classList.remove("visible");

    openModalBtn?.addEventListener("click", showModal);
    closeModalBtn?.addEventListener("click", hideModal);
    cancelBtn?.addEventListener("click", hideModal);

    modal?.addEventListener("click", (e) => {
        if (e.target === modal) hideModal();
    });


    misAvisosBtn?.addEventListener("click", () => {
        mostrandoMisAvisos = !mostrandoMisAvisos;
        const cards = document.querySelectorAll(".aviso-card");

        cards.forEach(card => {
            const idUsuario = parseInt(card.getAttribute("data-usuario"));
            const deleteBtn = card.querySelector(".btn-delete");

            if (mostrandoMisAvisos) {
                misAvisosBtn.textContent = "Todos los avisos";
                if (idUsuario === usuarioActual) {
                    card.style.display = "block";
                    deleteBtn.style.display = "inline-block";
                } else {
                    card.style.display = "none";
                }
            } else {
                misAvisosBtn.textContent = "Mis Avisos";
                card.style.display = "block";
                deleteBtn.style.display = "none";
            }
        });
    });


    avisosContainer?.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-delete")) {
            const idAviso = e.target.getAttribute("data-id");
            // 1. Obtener la tarjeta que se va a eliminar
            const card = e.target.closest(".aviso-card");

            const confirmacion = confirm("¿Seguro que deseas eliminar este aviso?");
            
            if (confirmacion) {
                // Preparamos los datos para enviar
                const formData = new FormData();
                formData.append('id_aviso', idAviso);

                // Realizamos la petición fetch
                fetch('/resident/avisos/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 2. Si el servidor confirma, eliminamos la tarjeta del DOM
                        card.style.opacity = '0'; // Opcional: para una transición
                        card.addEventListener('transitionend', () => card.remove());
                        // Si no usas transición, solo usa: card.remove();
                        
                        showGlobalPopup('Aviso Eliminado', data.message, 'Entendido', 'success');
                    } else {
                        // 3. Si falla (p.ej. no es su aviso), mostramos error
                        showGlobalPopup('Error al Eliminar', data.message, 'Entendido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    showGlobalPopup('Error de Red', 'Ocurrió un error al comunicarse con el servidor.', 'Entendido', 'error');
                });
            }
        }
    });


    form?.addEventListener("submit", (event) => {
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
            if (data.success) {
                showGlobalPopup('Aviso Creado', data.message, 'Entendido', 'success'); 
                hideModal();
                form.reset();
                if (window.loadSection) {
                    window.loadSection('avisos');
                }
            } else {
                showGlobalPopup('Error al Crear Aviso', data.message, 'Entendido', 'error');
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            showGlobalPopup('Error de Red', 'Ocurrió un error al comunicarse con el servidor.', 'Entendido', 'error');
        })
        .finally(() => {
            submitButton.textContent = 'Publicar Aviso';
            submitButton.disabled = false;
        });
    });


    document.querySelectorAll(".aviso-card").forEach((card, i) => {
        card.style.animationDelay = `${i * 100}ms`;
    });

})();