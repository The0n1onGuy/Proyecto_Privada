function initializeView() {

    // --- FUNCIONES PARA ABRIR MODAL DE RESIDENTE ---
    function openResidentModal(residentId) {
        $.ajax({
            url: `/admin/api/resident/${residentId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    populateResidentModal(response.data);
                    $('#residentModal').addClass('visible');
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'No se pudo obtener la información del residente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += '\nError del servidor: ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    }

    // --- FUNCIONES PARA LLENAR EL MODAL CON INFORMACIÓN ---
    function populateResidentModal(data) {
        $('#residentId').val(data.id_info);
        $('#nombreCompleto').val(`${data.nombres} ${data.apellido_p} ${data.apellido_m}`);
        $('#estatus').val(data.estatus);
        $('#tipo').val(data.es_propietario);
        $('#num_casa').val(data.num_casa);

        // Teléfonos
        const telefonosContainer = $('#telefonos-container');
        telefonosContainer.empty();
        if (data.telefonos && data.telefonos.length > 0) {
            let telefonosHtml = `<div class="form-group dynamic-input"><label for="telefono">Teléfono</label><div class="input-group">`;
            if (data.telefonos.length > 1) {
                telefonosHtml += `<select id="telefonoSelector" class="form-control-selector">`;
                data.telefonos.forEach(tel => {
                    telefonosHtml += `<option value="${tel.id_telefono}" data-value="${tel.telefono}">${tel.telefono}</option>`;
                });
                telefonosHtml += `</select>`;
            }
            const primerTelefono = data.telefonos[0];
            telefonosHtml += `<input type="text" id="telefono" name="telefono" value="${primerTelefono.telefono}" class="form-control-input">
                              <input type="hidden" id="id_telefono" name="id_telefono" value="${primerTelefono.id_telefono}">
                              </div></div>`;
            telefonosContainer.html(telefonosHtml);
        }

        // Correos
        const correosContainer = $('#correos-container');
        correosContainer.empty();
        if (data.correos && data.correos.length > 0) {
            let correosHtml = `<div class="form-group dynamic-input"><label for="correo">Correo</label><div class="input-group">`;
            if (data.correos.length > 1) {
                correosHtml += `<select id="correoSelector" class="form-control-selector">`;
                data.correos.forEach(email => {
                    correosHtml += `<option value="${email.id_correo}" data-value="${email.correo}">${email.correo}</option>`;
                });
                correosHtml += `</select>`;
            }
            const primerCorreo = data.correos[0];
            correosHtml += `<input type="email" id="correo" name="correo" value="${primerCorreo.correo}" class="form-control-input">
                            <input type="hidden" id="id_correo" name="id_correo" value="${primerCorreo.id_correo}">
                            </div></div>`;
            correosContainer.html(correosHtml);
        }

        // Cambiar valor al seleccionar
        $('#telefonoSelector').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            $('#telefono').val(selectedOption.data('value'));
            $('#id_telefono').val(selectedOption.val());
        });
        $('#correoSelector').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            $('#correo').val(selectedOption.data('value'));
            $('#id_correo').val(selectedOption.val());
        });
    }

    // --- BOTÓN EDITAR RESIDENTE ---
    $('#content-wrap').on('click', '#tablaResidentes .btn-edit', function() {
        const residentId = $(this).data('id');
        if (residentId) openResidentModal(residentId);
    });

    // --- CERRAR MODAL DE RESIDENTE ---
    $(document).on('click', '#closeModalBtn, #cancelBtn', function() {
        $('#residentModal').removeClass('visible');
    });

    // --- VALIDACIÓN DE CONTRASEÑA EN TIEMPO REAL PARA ACTUALIZACIÓN ---
    $('#resident_password').on('input', function() {
        const password = $(this).val();
        const errors = [];
        const passwordMessage = $('#resident_password_message');

        if (password.length < 8) errors.push("Debe tener al menos 8 caracteres");
        if (!/[A-Z]/.test(password)) errors.push("Debe incluir al menos una letra mayúscula");
        if (!/[0-9]/.test(password)) errors.push("Debe incluir al menos un número");
        if (!/[!@#$%^&*(),.?\":{}|<>_\-]/.test(password)) errors.push("Debe incluir al menos un carácter especial");

        if (errors.length > 0) {
            passwordMessage.text("❌ " + errors.join(" | ")).css("color", "red");
        } else {
            passwordMessage.text("✅ Contraseña válida").css("color", "green");
        }
    });

    // --- ENVÍO DEL FORMULARIO DE RESIDENTE CON VALIDACIONES ---
    $(document).on('submit', '#residentForm', function(e) {
        e.preventDefault();

        // 1. Validación de campos obligatorios
        let isValid = true;
        $(this).find('input[required], select[required]').each(function() {
            if (!$(this).val()) { isValid = false; $(this).css('border-color', 'red'); } 
            else { $(this).css('border-color', ''); }
        });
        if (!isValid) { alert('Por favor, complete todos los campos obligatorios.'); return; }

        // 2. Validación de teléfonos (10 dígitos)
        const phone = $('#telefono').val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if (phone && !phoneRegex.test(phone)) {
            alert('El número telefónico debe tener exactamente 10 dígitos numéricos.');
            return;
        }

        // 3. Validación de contraseña (si se ingresó)
        const password = $('#resident_password').val();
        const passwordMessage = $('#resident_password_message');
        if (password) {
            const validPassword =
                password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*(),.?":{}|<>_\-]/.test(password);
            if (!validPassword) {
                alert("Por favor, asegúrese de que la contraseña cumpla todos los requisitos.");
                passwordMessage.text("❌ Contraseña inválida. Revise los requisitos.").css("color", "red");
                return;
            } else {
                passwordMessage.text("✅ Contraseña válida").css("color", "green");
            }
        }

        // 4. Enviar datos al servidor
        const formData = $(this).serialize();
        $.ajax({
            url: '/admin/residentes/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#residentModal').removeClass('visible');
                if (response.success) {
                    showResultPopup('¡Éxito!', response.message, 'success');
                    loadSection(window.location.hash.substring(1) || 'residents');
                } else {
                    showResultPopup('Error', response.message, 'error');
                }
            },
            error: function() {
                $('#residentModal').removeClass('visible');
                showResultPopup('Error de Conexión', 'Ocurrió un error al contactar al servidor.', 'error');
            }
        });
    });

    // --- VARIABLES PARA MODAL NUEVO RESIDENTE ---
    const addModal = $('#addResidentModal');
    const addForm = $('#addResidentForm');

    // --- ABRIR MODAL ---
    $('#addResidentBtn').on('click', function() {
        addForm[0].reset();
        addForm.find('input, select').css('border-color', '');
        addModal.addClass('visible');
    });

    // --- CERRAR MODAL ---
    function hideAddModal() { addModal.removeClass('visible'); }
    $('#closeAddModalBtn, #cancelAddBtn').on('click', hideAddModal);

    // --- VALIDACIÓN DE CONTRASEÑA EN TIEMPO REAL ---
    const passwordInput = document.getElementById("add_password");
    const passwordMessage = document.getElementById("password_message");
    passwordInput.addEventListener("input", function () {
        const password = passwordInput.value;
        const errors = [];

        if (password.length < 8) errors.push("Debe tener al menos 8 caracteres");
        if (!/[A-Z]/.test(password)) errors.push("Debe incluir al menos una letra mayúscula");
        if (!/[0-9]/.test(password)) errors.push("Debe incluir al menos un número");
        if (!/[!@#$%^&*(),.?\":{}|<>_\-]/.test(password)) errors.push("Debe incluir al menos un carácter especial");

        if (errors.length > 0) {
            passwordMessage.textContent = "❌ " + errors.join(" | ");
            passwordMessage.style.color = "red";
        } else {
            passwordMessage.textContent = "✅ Contraseña válida";
            passwordMessage.style.color = "green";
        }
    });

    // --- ENVÍO FORMULARIO NUEVO RESIDENTE ---
    addForm.on('submit', function(e) {
        e.preventDefault();

        // 1. Validar campos obligatorios
        let isValid = true;
        addForm.find('input[required], select[required]').each(function() {
            if (!$(this).val()) { isValid = false; $(this).css('border-color', 'red'); } 
            else { $(this).css('border-color', ''); }
        });
        if (!isValid) { alert('Por favor, complete todos los campos obligatorios.'); return; }

        // 2. Validar teléfonos (10 dígitos)
        const phone1 = $('#add_phone1').val().trim();
        const phone2 = $('#add_phone2').val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            alert('Cada número telefónico debe tener exactamente 10 dígitos numéricos (sin letras ni símbolos).');
            return;
        }

        // 3. Validación final de contraseña
        const password = $('#add_password').val();
        const validPassword =
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[0-9]/.test(password) &&
            /[!@#$%^&*(),.?":{}|<>_\-]/.test(password);
        if (!validPassword) {
            alert("Por favor, asegúrese de que la contraseña cumpla todos los requisitos antes de continuar.");
            passwordMessage.textContent = "❌ Contraseña inválida. Revise los requisitos.";
            passwordMessage.style.color = "red";
            return;
        }

        // 4. Crear payload
        const payload = {
            nombres: $('#add_nombres').val(),
            apellido_p: $('#add_apellido_p').val(),
            apellido_m: $('#add_apellido_m').val(),
            fecha_nac: $('#add_fecha_nac').val(),
            username: $('#add_username').val(),
            password: password,
            rol: "Usuario",
            num_casa: $('#add_num_casa').val(),
            es_propietario: $('input[name="es_propietario"]:checked').val(),
            privada: $('#add_privada').val(),
            estatus: $('#add_estatus').val(),
            correos: [],
            telefonos: []
        };
        if ($('#add_email1').val()) payload.correos.push($('#add_email1').val().trim());
        if ($('#add_email2').val()) payload.correos.push($('#add_email2').val().trim());
        if (phone1) payload.telefonos.push(phone1);
        if (phone2) payload.telefonos.push(phone2);

        // 5. Enviar al servidor
        fetch('/admin/residentes/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideAddModal();
                showResultPopup('¡Éxito!', 'Residente creado exitosamente.', 'success');
            } else {
                showResultPopup('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResultPopup('Error de Conexión', 'Ocurrió un error de comunicación.', 'error');
        });
    });

    // --- FORMULARIO RESIDENTE EXTRA ---
    const addExtraModal = $('#addExtraModal');
    const addExtraForm = $('#addExtraForm');

    // Abrir modal extra
    $('#addExtraBtn').on('click', function() {
        addExtraForm[0].reset();
        addExtraForm.find('input, select').css('border-color', '');
        addExtraModal.addClass('visible');
    });

    // Cerrar modal extra
    function hideAddExtraModal() { addExtraModal.removeClass('visible'); }
    $('#closeExtraModalBtn, #cancelExtraBtn').on('click', hideAddExtraModal);

    // Enviar formulario residente extra
    addExtraForm.on('submit', function(e) {
        e.preventDefault();

        // 1. Validar campos obligatorios
        let isValid = true;
        addExtraForm.find('input[required], select[required]').each(function() {
            if (!$(this).val()) { isValid = false; $(this).css('border-color', 'red'); } 
            else { $(this).css('border-color', ''); }
        });
        if (!isValid) { alert('Por favor, complete todos los campos obligatorios.'); return; }

        // 2. Validar teléfonos
        const phone1 = $('#addExtraForm #extra_add_phone1').val().trim();
        const phone2 = $('#addExtraForm #extra_add_phone2').val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            alert('Cada número telefónico debe tener exactamente 10 dígitos numéricos (sin letras ni símbolos).');
            return;
        }

        // 3. Crear payload
        const payload = {
            nombres: $('#addExtraForm #extra_add_nombres').val(),
            apellido_p: $('#addExtraForm #extra_add_apellido_p').val(),
            apellido_m: $('#addExtraForm #extra_add_apellido_m').val(),
            es_propietario: $('input[name="es_propietario"]:checked', addExtraForm).val(),
            num_casa: $('#addExtraForm #add_extra_num_casa').val(),
            privada: $('#addExtraForm #extra_add_privada').val(),
            estatus: $('#addExtraForm #extra_add_estatus').val(),
            correos: [],
            telefonos: []
        };
        if ($('#addExtraForm #extra_add_email1').val()) payload.correos.push($('#addExtraForm #extra_add_email1').val().trim());
        if ($('#addExtraForm #extra_add_email2').val()) payload.correos.push($('#addExtraForm #extra_add_email2').val().trim());
        if (phone1) payload.telefonos.push(phone1);
        if (phone2) payload.telefonos.push(phone2);

        // 4. Enviar al servidor
        fetch('/admin/residentes/createEX', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideAddExtraModal();
                showResultPopup('¡Éxito!', 'Residente extra creado exitosamente.', 'success');
            } else {
                showResultPopup('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResultPopup('Error de Conexión', 'Ocurrió un error de comunicación.', 'error');
        });
    });

    // --- ELIMINAR RESIDENTE ---
    $('#tablaResidentes tbody').on('click', '.btn-delete', function() {
        const button = $(this);
        const idToDelete = button.data('id');
        const row = button.closest('tr');
        const nombreCompleto = row.find('td:eq(1)').text(); // Nombre columna 2

        if (confirm(`¿Estás seguro de que quieres eliminar a ${nombreCompleto}?`)) {
            const payload = { id_info: idToDelete };
            fetch('/admin/residentes/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResultPopup('¡Éxito!', 'Residente eliminado exitosamente.', 'success');
                } else {
                    showResultPopup('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error al eliminar:', error);
                showResultPopup('Error de Conexión', 'Ocurrió un error de comunicación.', 'error');
            });
        }
    });
}