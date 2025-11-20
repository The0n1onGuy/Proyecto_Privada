function initializeView() {
    // ------------------------------------------------------------
    // Definición de íconos SVG reutilizables para mostrar mensajes visuales
    // ------------------------------------------------------------
    const iconoError = `
<svg height="25px" width="25px" fill="#ff0000" viewBox="-3.5 0 19 19" xmlns="http://www.w3.org/2000/svg" stroke="#ff0000" style="vertical-align:middle;">
<path d="M11.383 13.644A1.03 1.03 0 0 1 9.928 15.1L6 11.172 2.072 15.1a1.03 1.03 0 1 1-1.455-1.456l3.928-3.928L.617 5.79a1.03 1.03 0 1 1 1.455-1.456L6 8.261l3.928-3.928a1.03 1.03 0 0 1 1.455 1.456L7.455 9.716z"></path>
</svg>`;

    const iconoCorrecto = `
<svg height="20px" width="20px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css"> .st0{fill:#2bff00;} </style> <g> <path class="st0" d="M469.402,35.492C334.09,110.664,197.114,324.5,197.114,324.5L73.509,184.176L0,254.336l178.732,222.172 l65.15-2.504C327.414,223.414,512,55.539,512,55.539L469.402,35.492z"></path> </g> </g></svg>`;

    const iconoAdvertencia = `
<svg height="25px" width="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#898234" style="vertical-align:middle;">
<path fill-rule="evenodd" clip-rule="evenodd" d="M9.82664 2.22902C10.7938 0.590326 13.2063 0.590325 14.1735 2.22902L23.6599 18.3024C24.6578 19.9933 23.3638 22 21.4865 22H2.51362C0.63634 22 -0.657696 19.9933 0.340215 18.3024L9.82664 2.22902ZM10.0586 7.05547C10.0268 6.48227 10.483 6 11.0571 6H12.9429C13.517 6 13.9732 6.48227 13.9414 7.05547L13.5525 14.0555C13.523 14.5854 13.0847 15 12.554 15H11.446C10.9153 15 10.477 14.5854 10.4475 14.0555L10.0586 7.05547ZM14 18C14 19.1046 13.1046 20 12 20C10.8954 20 10 19.1046 10 18C10 16.8954 10.8954 16 12 16C13.1046 16 14 16.8954 14 18Z" fill="#fff833"></path>
</svg>`;

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
                    showResultPopup('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'No se pudo obtener la información del residente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += '\nError del servidor: ' + xhr.responseJSON.message;
                }
                showResultPopup('Error de Conexión', errorMessage, 'error');
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

        if (errors.length > 0) {
            passwordMessage.html(`${iconoError} ${errors.join(" | ")}`).css("color", "red");
        } else {
            passwordMessage.html(`${iconoCorrecto} Contraseña válida`).css("color", "green");
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
        if (!isValid) { showResultPopup('Error', 'Por favor, complete todos los campos obligatorios.', 'error'); return; }

        // 2. Validación de teléfonos (10 dígitos)
        const phone = $('#telefono').val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if (phone && !phoneRegex.test(phone)) {
            showResultPopup('Error', 'El número telefónico debe tener exactamente 10 dígitos numéricos.', 'error');
            return;
        }

        // 3. Validación de contraseña (si se ingresó)
        const password = $('#resident_password').val();
        const passwordMessage = $('#resident_password_message');
        if (password) {
            const validPassword =
                password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[0-9]/.test(password);
            if (!validPassword) {
                showResultPopup("Error", "Por favor, asegúrese de que la contraseña cumpla todos los requisitos.", "error");
                passwordMessage.html(`${iconoError} Contraseña inválida. Revise los requisitos.`).css("color", "red");
                return;
            } else {
                passwordMessage.html(`${iconoCorrecto} Contraseña válida`).css("color", "green");
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

    // Recargar la página después de 2 segundos
    setTimeout(() => {
        location.reload();
    }, 1500);
}else {
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

    // --- VALIDACIÓN DE CONTRASEÑA NUEVO RESIDENTE ---
    const passwordInput = document.getElementById("add_password");
    const passwordMessage = document.getElementById("password_message");

    passwordInput.addEventListener("input", function () {
        const password = passwordInput.value;
        const errors = [];

        if (password.length < 8) errors.push("Debe tener al menos 8 caracteres");
        if (!/[A-Z]/.test(password)) errors.push("Debe incluir al menos una letra mayúscula");
        if (!/[0-9]/.test(password)) errors.push("Debe incluir al menos un número");

        if (errors.length > 0) {
            passwordMessage.innerHTML = `${iconoError} ${errors.join(" | ")}`;
            passwordMessage.style.color = "red";
        } else {
            passwordMessage.innerHTML = `${iconoCorrecto} Contraseña válida`;
            passwordMessage.style.color = "green";
        }
    });
// ------------------------------------------------------------
// Verificación de duplicidad del nombre de usuario
// ------------------------------------------------------------
$('#add_username').on('blur', function() {
    const usernameNuevo = $(this).val().trim();

    if (!usernameNuevo) return; // No hacer nada si está vacío

    fetch('/admin/residentes/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: usernameNuevo, verificar: true })
    })
    .then(response => response.json())
    .then(data => {
        if (data.existe) {
            showResultPopup(
                'Usuario duplicado',
                'El nombre de usuario ya está registrado. Por favor, elija otro.',
                'error'
            );
            $('#add_username').css('border-color', 'red');
        } else {
            $('#add_username').css('border-color', 'green');
        }
    })
    .catch(error => {
        console.error('Error al verificar usuario:', error);
        showResultPopup(
            'Error de Conexión',
            'No se pudo verificar el usuario. Inténtelo nuevamente.',
            'error'
        );
    });
});

// ------------------------------------------------------------
// Verificación de duplicidad del propietario
// ------------------------------------------------------------
$('#add_num_casa').on('blur', function() {
    const numCasaNuevo = $(this).val().trim();

    if (!numCasaNuevo) return;

    fetch('/admin/residentes/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ num_casa: numCasaNuevo, verificar_casa: true })
    })
    .then(response => response.json())
    .then(data => {
        if (data.existe) {
            showResultPopup(
                'Número de casa duplicado',
                'Este número de casa ya tiene propietario. Por favor, elija otro.',
                'error'
            );
            $('#add_num_casa').css('border-color', 'red');
        } else {
            $('#add_num_casa').css('border-color', 'green');
        }
    })
    .catch(error => {
        console.error('Error al verificar número de casa:', error);
        showResultPopup(
            'Error de Conexión',
            'No se pudo verificar el número de casa. Inténtelo nuevamente.',
            'error'
        );
    });
});

// $('#num_casa').on('blur', function() {
//     const numCasaNuevo = $(this).val().trim();

//     if (!numCasaNuevo) return;

//     fetch('/admin/residentes/create', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ num_casa: numCasaNuevo, verificar_casa: true })
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.existe) {
//             showResultPopup(
//                 'Número de casa duplicado',
//                 'Este número de casa ya tiene propietario. Por favor, elija otro.',
//                 'error'
//             );
//             $('#num_casa').css('border-color', 'red');
//         } else {
//             $('#num_casa').css('border-color', 'green');
//         }
//     })
//     .catch(error => {
//         console.error('Error al verificar número de casa:', error);
//         showResultPopup(
//             'Error de Conexión',
//             'No se pudo verificar el número de casa. Inténtelo nuevamente.',
//             'error'
//         );
//     });
// });

// Validación de edad al seleccionar la fecha de nacimiento
const fechaNacimientoInput = document.getElementById("add_fecha_nac");
const edadMessage = document.createElement("div"); 
edadMessage.style.marginTop = "5px";
fechaNacimientoInput.parentNode.appendChild(edadMessage);

// Función para calcular edad
function calcularEdad(fecha) {
    const hoy = new Date();
    const fechaNacimiento = new Date(fecha);
    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
    const mes = hoy.getMonth() - fechaNacimiento.getMonth();
    const dia = hoy.getDate() - fechaNacimiento.getDate();

    if (mes < 0 || (mes === 0 && dia < 0)) {
        edad--;
    }
    return edad;
}

// Mostrar mensaje en tiempo real
fechaNacimientoInput.addEventListener("change", function () {
    if (!this.value) {
        edadMessage.innerHTML = "";
        return;
    }
    const edad = calcularEdad(this.value);
    if (edad >= 18) {
        edadMessage.innerHTML = `<span style="color:green; font-weight:bold;">Residente mayor de edad (${edad} años)</span>`;
    } else {
        edadMessage.innerHTML = `<span style="color:red; font-weight:bold;">Residente menor de edad (${edad} años)</span>`;
    }
});


// --- ENVÍO FORMULARIO NUEVO RESIDENTE ---
    addForm.on('submit', function(e) {
        e.preventDefault();

        const fechaNacimiento = fechaNacimientoInput.value;
    if (!fechaNacimiento) return; // Ya validado por required

    const edad = calcularEdad(fechaNacimiento);
    if (edad < 18) {
        e.preventDefault(); // Evita el envío
        showResultPopup('Error', 'No se puede registrar un residente menor de edad.', 'error');
        fechaNacimientoInput.focus();
        return;
    }


        // Validar campos obligatorios
        let isValid = true;
        addForm.find('input[required], select[required]').each(function() {
            if (!$(this).val()) { isValid = false; $(this).css('border-color', 'red'); } 
            else { $(this).css('border-color', ''); }
        });
        if (!isValid) { showResultPopup('Error', 'Por favor, complete todos los campos obligatorios.', 'error'); return; }

        // Validar teléfonos
        const phone1 = $('#add_phone1').val().trim();
        const phone2 = $('#add_phone2').val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            showResultPopup('Error', 'Cada número telefónico debe tener exactamente 10 dígitos numéricos (sin letras ni símbolos).', 'error');
            return;
        }

        // Validar contraseña
        const password = $('#add_password').val();
        const validPassword =
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[0-9]/.test(password);
        if (!validPassword) {
            showResultPopup("Error", "Por favor, asegúrese de que la contraseña cumpla todos los requisitos.", "error");
            passwordMessage.innerHTML = `${iconoError} Contraseña inválida. Revise los requisitos.`;
            passwordMessage.style.color = "red";
            return;
        }

        // Crear payload
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

        // Enviar al servidor
        fetch('/admin/residentes/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            hideAddModal();
            if (data.success) {
    showResultPopup('¡Éxito!', 'Residente creado exitosamente.', 'success');

    // Recargar la página después de 2 segundos
    setTimeout(() => {
        location.reload();
    }, 2000);
}
 else {
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

    // Remueve la fila inmediatamente
    row.remove();

    // Recarga la página después de 2 segundos
    setTimeout(() => {
        location.reload();
    }, 2000);
}
 else {
                    showResultPopup('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error al eliminar:', error);
                showResultPopup('Error de Conexión', 'Ocurrió un error de comunicación.', 'error');
            });
        }
    });
    
    // ------------------------------------------------------------
    // Validación en tiempo real de números telefónicos
    // ------------------------------------------------------------
    function validarTelefonoEnTiempoReal(inputId, messageId) {
        const phoneInput = document.getElementById(inputId);
        const message = document.getElementById(messageId);
        const phoneRegex = /^[0-9]{10}$/;

        if (!phoneInput || !message) return; // Evita errores si los elementos no existen

        phoneInput.addEventListener("input", function () {
            const phone = phoneInput.value.trim();

            if (phone === "") {
                message.innerHTML = "";
            } else if (!/^[0-9]*$/.test(phone)) {
                message.innerHTML = `
                    <div style="display:flex; align-items:center; gap:8px;">
                        ${iconoError}
                        <span style="color:red;">Solo se permiten números</span>
                    </div>`;
            } else if (!phoneRegex.test(phone)) {
                message.innerHTML = `
                    <div style="display:flex; align-items:center; gap:8px;">
                        ${iconoAdvertencia}
                        <span style="color:orange;">El número debe tener 10 dígitos</span>
                    </div>`;
            } else {
                message.innerHTML = `
                    <div style="display:flex; align-items:center; gap:8px;">
                        ${iconoCorrecto}
                        <span style="color:green;">Número válido</span>
                    </div>`;
            }
        });
    }

    // Activar validación en los campos
    validarTelefonoEnTiempoReal("add_phone1", "phone1_message");
    validarTelefonoEnTiempoReal("add_phone2", "phone2_message");
    validarTelefonoEnTiempoReal("extra_add_phone1", "phone1_messageextra");
    validarTelefonoEnTiempoReal("extra_add_phone2", "phone2_messageextra");
    validarTelefonoEnTiempoReal("inputTelefono", "mensajeTelefonoExistente");

// --- FUNCIONALIDAD PARA MOSTRAR FORMULARIO SEGÚN SELECCIÓN DE PROPIETARIO ---
function toggleResidentForm() {
    const propietarioSi = document.getElementById('propietario_si');
    const propietarioNo = document.getElementById('propietario_no');
    const propietarioForm = document.getElementById('addResidentForm');
    const extraForm = document.getElementById('addExtraForm');

    function actualizarFormulario() {
        if (propietarioSi.checked) {
            propietarioForm.style.display = 'block';
            extraForm.style.display = 'none';
        } else {
            propietarioForm.style.display = 'none';
            extraForm.style.display = 'block';
        }
    }

    // Inicializar según valor por defecto
    actualizarFormulario();

    // Escuchar cambios en los radios
    propietarioSi.addEventListener('change', actualizarFormulario);
    propietarioNo.addEventListener('change', actualizarFormulario);
}

// Llamar la función al abrir modal
$('#addResidentBtn').on('click', function() {
    addForm[0].reset();
    addForm.find('input, select').css('border-color', '');
    addModal.addClass('visible');

    toggleResidentForm(); // Activar lógica de propietario / extra
});

}


// ============================================================
// Configuración global personalizada (agregada por el usuario)
// ============================================================
if (window.DataTable) {
    $.extend(true, window.DataTable.defaults, {
        bLengthChange: false, // Oculta el menú "Mostrar X registros"
        bInfo: false          // Oculta el texto "Mostrando registros del 1 al X..."
    });
}