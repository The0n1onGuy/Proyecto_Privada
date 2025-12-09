(function() {
    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const modal = document.getElementById("createAvisoModal");
    const form = document.getElementById("createAvisoForm");
    const misAvisosBtn = document.getElementById("misAvisosBtn");
    const avisosContainer = document.getElementById("avisosContainer");

    let mostrandoMisAvisos = false;

    
    const avisosContent = document.querySelector(".avisos-content");
    const usuarioActual = avisosContent?.getAttribute("data-session-user-id") || "";

    const showModal = () => modal?.classList.add("visible");
    const hideModal = () => modal?.classList.remove("visible");

    openModalBtn?.addEventListener("click", showModal);
    closeModalBtn?.addEventListener("click", hideModal);
    cancelBtn?.addEventListener("click", hideModal);

    modal?.addEventListener("click", (e) => {
        if (e.target === modal) hideModal();
    });

    // LÓGICA DEL FILTRO 
    misAvisosBtn?.addEventListener("click", () => {
        mostrandoMisAvisos = !mostrandoMisAvisos;
        const cards = document.querySelectorAll(".aviso-card");

        // Cambia texto del botón según el estado
        if (mostrandoMisAvisos) {
            misAvisosBtn.textContent = "Ver Todos";
            misAvisosBtn.classList.add('active'); 
        } else {
            misAvisosBtn.textContent = "Mis Avisos";
            misAvisosBtn.classList.remove('active');
        }

        cards.forEach(card => {
            // Quitamos parseInt. Leemos el ID de la tarjeta tal cual.
            const idUsuarioTarjeta = card.getAttribute("data-usuario") || "";

            if (mostrandoMisAvisos) {
                // MODO FILTRO: Comparamos texto con texto
                if (idUsuarioTarjeta === usuarioActual) {
                    card.style.display = ""; // Quita 'none', deja que el CSS decida (visible)
                } else {
                    card.style.display = "none"; // Oculta
                }
            } else {
                //Limpiamos el estilo para que se vean todas
                card.style.display = ""; 
            }
        });
    });

    // Lógica de eliminar aviso
    avisosContainer?.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-eliminar-aviso")) {
            const idAviso = e.target.getAttribute("data-id");
            
            if (confirm("¿Estás seguro de eliminar este aviso?")) {
                const formData = new FormData();
                formData.append('id_aviso', idAviso);

                fetch('/admin/avisos/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showGlobalPopup('Éxito', data.message, 'Entendido', 'success');
                        // Eliminar tarjeta del DOM visualmente
                        const card = e.target.closest('.aviso-card');
                        if (card) card.remove();
                    } else {
                        showGlobalPopup('Error', data.message, 'Entendido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
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

        fetch('/admin/avisos/create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showGlobalPopup('Aviso Creado', data.message, 'Entendido', 'success'); 
                hideModal();
                form.reset();
                // Recargar la sección si existe la función global
                if (window.loadSection) {
                    window.loadSection('avisos');
                } else {
                    // Fallback si no hay carga dinámica: recargar página
                    window.location.reload();
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

    // Animación de entrada
    document.querySelectorAll(".aviso-card").forEach((card, i) => {
        card.style.animationDelay = `${i * 100}ms`;
    });

})();