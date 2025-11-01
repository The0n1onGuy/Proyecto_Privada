$(document).ready(function() {
    const modal = $('#collaboratorModal');
    const form = $('#collaboratorForm');
    
    const collaboratorIdInput = $('#collaboratorId');

    let originalContacts = {};

    //--------------------------------------------------------------------------------------Boton de editar
    $('#tablaColaboradores tbody').on('click', '.btn-edit', function() {
        
        //Llama el JSON data
        const collaboratorData = $(this).data('colaborador');
         originalContacts = {
            emails: collaboratorData.correos ? [...collaboratorData.correos] : [],
            phones: collaboratorData.telefonos ? [...collaboratorData.telefonos] : []
        };
        
        //Llenalos en campos de formulario
        $('#collaboratorId').val(collaboratorData.id_usuario);
        $('#nombres').val(collaboratorData.nombres);
        $('#apellido_p').val(collaboratorData.apellido_p);
        $('#apellido_m').val(collaboratorData.apellido_m);
        $('#rol').val(collaboratorData.rol);
        $('#privada').val(collaboratorData.privada_nombre);
        $('#estatus').val(collaboratorData.estatus);
        
        //Llenalos respectivamente en las listas del modal 
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

    // Handle edit/delete/cancel actions
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

    // When changing selection, reflect in input (if not editing or deleting)
    $('#selectCorreo, #selectTelefono').on('change', function() {
        const type = (this.id === 'selectCorreo') ? 'email' : 'phone';
        if (contactStates[type] === 'view') {
            const input = (type === 'email') ? $('#inputCorreo') : $('#inputTelefono');
            input.val(getSelectedContactValue(type));
        }
    });

    //Funcion de llenado de contactos y sus entradas
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

    //Funcion de llenado de campo de confirmacion
    $('#selectCorreo, #selectTelefono').on('change', function() {
        const selectedText = $(this).find('option:selected').text();
        const input = (this.id === 'selectCorreo') ? $('#inputCorreo') : $('#inputTelefono');
        input.val(selectedText !== 'No hay registros' ? selectedText : '');
    });

    //Logica de ocultar
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
    // Aquí se agregará la validación de teléfonos (edición)
    // ------------------------------------------------------------
    // En esta parte se implementará la misma lógica del formulario de agregar,
    // para verificar que los números telefónicos (actual y nuevo) tengan
    // exactamente 10 dígitos numéricos y no contengan letras ni símbolos.
    // ------------------------------------------------------------

    $('#collaboratorForm').on('submit', function(e) {
        e.preventDefault();

        // Validación de número telefónico actual y nuevo
        const phoneActual = $('#inputTelefono').val();
        const phoneNuevo = $('#newTelefono').val();
        const phoneRegex = /^[0-9]{10}$/; // Solo permite 10 dígitos

        if ((phoneActual && !phoneRegex.test(phoneActual)) || (phoneNuevo && !phoneRegex.test(phoneNuevo))) {
            alert('Cada número telefónico debe tener exactamente 10 dígitos numéricos (sin letras ni símbolos).');
            return; // Detiene el envío si no cumple con los requisitos
        }

        // ------------------------------------------------------------
        // Continúa el proceso normal si pasa la validación
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
            correo_actual: $('#inputCorreo').val(),
            telefono_actual: $('#inputTelefono').val(),
            correo_nuevo: $('#newCorreo').val(),
            telefono_nuevo: $('#newTelefono').val(),
            correo_id: $('#selectCorreo').val(),
            telefono_id: $('#selectTelefono').val()
        };
        
        // --- PARA PRUEBAS POR SI NECESITAS VER LOS DATOS ENVIADOS 
        // console.log("Sending payload to server:", payload);
        // --------------------------------------------------------------
        
        const apiUrl = '/admin/servicios/update';

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                hideModal();
                location.reload(); 
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Ocurrió un error de comunicación con el servidor.');
        });
    });

});


