$(document).ready(function() {
    const modal = $('#collaboratorModal');
    const form = $('#collaboratorForm');

    let originalContacts = {};

    //--------------------------------------------------------------------------------------Boton de editar
    $('#tablaColaboradores tbody').on('click', '.btn-edit', function() {
        const collaboratorData = $(this).data('colaborador');
        originalContacts = {
            emails: collaboratorData.correos ? [...collaboratorData.correos] : [],
            phones: collaboratorData.telefonos ? [...collaboratorData.telefonos] : []
        };

        $('#collaboratorId').val(collaboratorData.id_usuario);
        $('#nombres').val(collaboratorData.nombres);
        $('#apellido_p').val(collaboratorData.apellido_p);
        $('#apellido_m').val(collaboratorData.apellido_m);
        $('#rol').val(collaboratorData.rol);
        $('#privada').val(collaboratorData.privada_nombre);
        $('#estatus').val(collaboratorData.estatus);

        populateContactSelector('email', originalContacts.emails);
        populateContactSelector('phone', originalContacts.phones);

        $('#modalTitle').text('Editar Colaborador');
        $('#collaboratorModal').addClass('visible');
    });

    const contactStates = { email: 'view', phone: 'view' };

    function setContactState(type, state) {
        const input = (type === 'email') ? $('#inputCorreo') : $('#inputTelefono');
        const cancelBtn = $(`.btn-cancel-contact[data-type="${type}"]`);
        const editBtn = $(`.btn-edit-contact[data-type="${type}"]`);
        const deleteBtn = $(`.btn-delete-contact[data-type="${type}"]`);

        contactStates[type] = state;

        if (state === 'view') {
            input.prop('disabled', true).val(getSelectedContactValue(type));
            cancelBtn.prop('disabled', true);
            editBtn.prop('disabled', false);
            deleteBtn.prop('disabled', false);
        } else if (state === 'edit') {
            input.prop('disabled', false);
            cancelBtn.prop('disabled', false);
            editBtn.prop('disabled', true);
            deleteBtn.prop('disabled', false);
        } else if (state === 'delete') {
            input.prop('disabled', true).val('A ELIMINAR');
            cancelBtn.prop('disabled', false);
            editBtn.prop('disabled', false);
            deleteBtn.prop('disabled', true);
        }
    }

    function getSelectedContactValue(type) {
        const select = (type === 'email') ? $('#selectCorreo') : $('#selectTelefono');
        return select.find('option:selected').text() || '';
    }

    $('.btn-edit-contact').on('click', function() {
        const type = $(this).data('type');
        setContactState(type, 'edit');
    });

    $('.btn-delete-contact').on('click', function() {
        const type = $(this).data('type');
        setContactState(type, 'delete');
    });

    $('.btn-cancel-contact').on('click', function() {
        const type = $(this).data('type');
        setContactState(type, 'view');
    });

    $('#selectCorreo, #selectTelefono').on('change', function() {
        const type = (this.id === 'selectCorreo') ? 'email' : 'phone';
        if (contactStates[type] === 'view') {
            const input = (type === 'email') ? $('#inputCorreo') : $('#inputTelefono');
            input.val(getSelectedContactValue(type));
        }
    });

    function populateContactSelector(type, items) {
        const select = (type === 'email') ? $('#selectCorreo') : $('#selectTelefono');
        const input = (type === 'email') ? $('#inputCorreo') : $('#inputTelefono');
        select.empty();
        select.find('option').show(); 

        if (items && items.length > 0) {
            items.forEach(item => {
                const key = (type === 'email') ? 'correo' : 'telefono';
                select.append(`<option value="${item['id_' + key]}">${item[key]}</option>`);
            });
        } else {
            select.append('<option value="">No hay registros</option>');
        }
        select.trigger('change');
    }

    $('#selectCorreo, #selectTelefono').on('change', function() {
        const selectedText = $(this).find('option:selected').text();
        const input = (this.id === 'selectCorreo') ? $('#inputCorreo') : $('#inputTelefono');
        input.val(selectedText !== 'No hay registros' ? selectedText : '');
    });

    function hideModal() {
        populateContactSelector('email', originalContacts.emails);
        populateContactSelector('phone', originalContacts.phones);
        modal.removeClass('visible');
    }

    $('#closeModalBtn, #cancelBtn').on('click', hideModal);
    $(document).on('keydown', function(e) { if (e.key === "Escape") hideModal(); });
    
    modal.on('click', function(e) {
        if ($(e.target).is(modal)) {
            hideModal();
        }
    });

    // ------------------------------------------------------------
    // VALIDACIÓN DE FORMULARIO
    // ------------------------------------------------------------
    $('#collaboratorForm').on('submit', function(e) {
        e.preventDefault();

        const phoneActual = $('#inputTelefono');
        const phoneNuevo = $('#newTelefono');
        const correoActual = $('#inputCorreo');
        const correoNuevo = $('#newCorreo');
        const phoneRegex = /^[0-9]{10}$/;
        const emailAction = contactStates.email;
        const phoneAction = contactStates.phone;

        const totalPhones = originalContacts.phones.length;
        const totalEmails = originalContacts.emails.length;

        // Resetear estilos de error
        phoneActual.removeClass('input-error');
        phoneNuevo.removeClass('input-error');
        correoActual.removeClass('input-error');
        correoNuevo.removeClass('input-error');

        // Validar eliminación total
        if ((phoneAction === 'delete' && totalPhones === 1 && !phoneNuevo.val())) {
            phoneActual.addClass('input-error');
            showResultPopup('Error de Validación', 'Debe conservar al menos un número telefónico registrado.', 'error');
            phoneActual.focus();
            return;
        }

        if ((emailAction === 'delete' && totalEmails === 1 && !correoNuevo.val())) {
            correoActual.addClass('input-error');
            showResultPopup('Error de Validación', 'Debe conservar al menos un correo electrónico registrado.', 'error');
            correoActual.focus();
            return;
        }

        // Validar formato de teléfono
        if ((phoneAction === 'edit' && phoneActual.val() && !phoneRegex.test(phoneActual.val()))) {
            phoneActual.addClass('input-error');
            showResultPopup('Error de Formato', 'El número telefónico debe tener exactamente 10 dígitos numéricos.', 'error');
            phoneActual.focus();
            return;
        }

        if (phoneNuevo.val() && !phoneRegex.test(phoneNuevo.val())) {
            phoneNuevo.addClass('input-error');
            showResultPopup('Error de Formato', 'El número telefónico nuevo debe tener exactamente 10 dígitos numéricos.', 'error');
            phoneNuevo.focus();
            return;
        }

        // ------------------------------------------------------------
        // Envío normal si pasa las validaciones
        // ------------------------------------------------------------
        const id = $('#collaboratorId').val();
        const payload = {
            id_usuario: id,
            nombres: $('#nombres').val(),
            apellido_p: $('#apellido_p').val(),
            apellido_m: $('#apellido_m').val(),
            rol: $('#rol').val(),
            privada: $('#privada').val(),
            estatus: $('#estatus').val(),
            email_action: contactStates.email,
            phone_action: contactStates.phone,
            correo_actual: correoActual.val(),
            telefono_actual: phoneActual.val(),
            correo_nuevo: correoNuevo.val(),
            telefono_nuevo: phoneNuevo.val(),
            correo_id: $('#selectCorreo').val(),
            telefono_id: $('#selectTelefono').val()
        };

        $.ajax({
            url: '/admin/colaboradores/update',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
    if (response.success) {
        showResultPopup('¡Éxito!', response.message, 'success');

        // Espera 1.5 segundos antes de recargar
        setTimeout(function() {
            location.reload();
        }, 1500);
    } else {
        showResultPopup('Error', response.message, 'error');
    }
    }
   });
});

});