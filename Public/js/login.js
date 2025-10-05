document.addEventListener('DOMContentLoaded', () => {
    // --- Elementos del DOM para el Popup de Error ---
    const popupOverlay = document.getElementById('errorPopup');
    const popupContent = popupOverlay ? popupOverlay.querySelector('.popup-content') : null;
    const popupMessageEl = document.getElementById('popupMessage');
    const closeButton = popupOverlay ? popupOverlay.querySelector('.popup-close-btn') : null;
    
    // --- Elementos del Formulario y Carga ---
    const loginForm = document.querySelector('form');
    const loadingPopup = document.getElementById('loadingPopup');
    const body = document.body;
    const errorMessage = body.dataset.errorMessage;

    /**
     * Muestra el popup de error con una animación de entrada.
     * @param {string} message El mensaje a mostrar en el popup.
     */
    function showPopup(message) {
        if (!popupOverlay || !popupMessageEl || !message) {
            console.error("Popup elements not found or message is empty.");
            return;
        }

        popupMessageEl.textContent = message;
        
        // Añade la clase 'visible' para activar la transición del overlay y la animación de entrada.
        popupOverlay.classList.add('visible');
    }

    /**
     * Oculta el popup de error con una animación de salida.
     */
    function hidePopup() {
        if (!popupOverlay || !popupContent) return;

        // 1. Añade la clase 'hiding' para iniciar la animación de salida en .popup-content
        popupOverlay.classList.add('hiding');

        // 2. Escucha a que la animación de salida termine en el .popup-content
        popupContent.addEventListener('animationend', () => {
            // 3. Una vez terminada, quita ambas clases para resetear el estado y ocultar el overlay
            popupOverlay.classList.remove('visible');
            popupOverlay.classList.remove('hiding');
        }, { once: true }); // 'once: true' asegura que el evento solo se ejecute una vez.
    }

    // --- Lógica de Carga al Enviar Formulario (se mantiene sin cambios) ---
    if (loginForm && loadingPopup) {
        loginForm.addEventListener('submit', (event) => {
            event.preventDefault();
            loadingPopup.classList.add('visible');
            setTimeout(() => {
                loginForm.submit();
            }, 700);
        });
    }

    // --- Asignación de Eventos para el Popup de Error ---
    if (closeButton) {
        closeButton.addEventListener('click', hidePopup);
    }
    
    if (popupOverlay) {
        // Cierra el popup si se hace clic en el fondo oscuro
        popupOverlay.addEventListener('click', (event) => {
            if (event.target === popupOverlay) {
                hidePopup();
            }
        });
    }

    // --- Lógica de Inicialización ---
    // Si el servidor envía un mensaje de error al cargar la página, lo mostramos.
    if (errorMessage) {
        showPopup(errorMessage);
    }
});

