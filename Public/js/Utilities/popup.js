/**
 * Muestra el popup global con contenido dinámico.
 * * @param {string} title El título del popup.
 * @param {string} message El mensaje principal.
 * @param {string} buttonText El texto para el botón de cierre.
 * @param {string} type El tipo de popup ('alert', 'notification', 'error', 'success'). Determina el color.
 * @param {string} popupId El ID del elemento popup (por defecto 'globalPopup').
 */
function showGlobalPopup(title, message, buttonText = 'Entendido', type = 'notification', popupId = 'globalPopup') {
    const popup = $('#' + popupId);
    if (!popup.length) {
        console.error('Popup element with ID ' + popupId + ' not found.');
        return;
    }

    const popupTitle = $('#' + popupId + 'Title');
    const popupMessage = $('#' + popupId + 'Message');
    const popupCloseBtn = $('#' + popupId + 'CloseBtn');
    const popupCloseBtnHeader = $('#' + popupId + 'CloseBtnHeader'); // Botón X
    const popupContent = popup.find('.popup-content');

    // Validar tipo
    const validTypes = ['alert', 'notification', 'error', 'success'];
    const popupType = validTypes.includes(type.toLowerCase()) ? type.toLowerCase() : 'notification';

    // Establecer contenido
    popupTitle.text(title);
    popupMessage.text(message);
    popupCloseBtn.text(buttonText);

    // Aplicar clase de tipo para el color
    popupContent.removeClass(validTypes.join(' ')).addClass(popupType);

    // Mostrar el popup
    popup.addClass('visible');

    // Funcionalidad de cierre unificada
    const hide = () => popup.removeClass('visible');
    
    // Asignar evento al botón principal y al botón X (removiendo previos para evitar duplicados)
    popupCloseBtn.off('click').on('click', hide);
    popupCloseBtnHeader.off('click').on('click', hide);
    
    // Cerrar al hacer clic fuera del contenido
    popup.off('click').on('click', function(e) {
        if ($(e.target).is(popup)) {
            hide();
        }
    });
    
    // Cerrar con la tecla Escape
    $(document).off('keydown.globalPopup').on('keydown.globalPopup', function(e) {
        if (e.key === "Escape" && popup.hasClass('visible')) {
            hide();
        }
    });
}

// Ejemplo de cómo llamarlo (esto iría en el JS de la sección específica donde lo necesites)
// $(document).ready(function() {
//     // showGlobalPopup('¡Atención!', 'Este es un mensaje de alerta.', 'De acuerdo', 'alert');
//     // showGlobalPopup('Notificación', 'La operación se completó.', 'OK', 'notification');
//     // showGlobalPopup('Error', 'No se pudo procesar la solicitud.', 'Cerrar', 'error');
// });