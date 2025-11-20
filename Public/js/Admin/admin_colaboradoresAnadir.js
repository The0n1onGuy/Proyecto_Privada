// ------------------------------------------------------------
// Bloque principal que espera a que el DOM esté completamente cargado
// ------------------------------------------------------------
$(document).ready(function() { 

    // Referencias a los elementos del DOM utilizados en el flujo principal
    const addModal = $('#addCollaboratorModal');    // Modal para agregar colaborador
    const addForm = $('#addCollaboratorForm');      // Formulario de creación de colaborador
    const form = $('#collaboratorForm');            // Formulario general utilizado dentro del modal

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

    // ------------------------------------------------------------
    // Apertura del modal
    // ------------------------------------------------------------
    $('#addCollaboratorBtn').on('click', function() {
        form[0].reset();
        addForm.find('input, select').css('border-color', '');
        addModal.addClass('visible');
    });

    // ------------------------------------------------------------
    // Cierre del modal
    // ------------------------------------------------------------
    function hideAddModal() {
        addModal.removeClass('visible');
    }

    $('#closeAddModalBtn, #cancelAddBtn').on('click', hideAddModal);
    $(document).on('keydown', function(e) { if (e.key === "Escape") hideAddModal(); });
    addModal.on('click', function(e) { if ($(e.target).is(addModal)) hideAddModal(); });

    // ------------------------------------------------------------
    // Eliminar colaborador con confirmación
    // ------------------------------------------------------------
    $('#tablaColaboradores tbody').on('click', '.btn-delete', function() {
        const button = $(this);
        const idToDelete = button.data('id');
        const row = button.closest('tr');
        const nombreCompleto = row.find('td:eq(1)').text() + ' ' + row.find('td:eq(2)').text();

        if (confirm(`¿Estás seguro de que quieres eliminar a ${nombreCompleto}? Esta acción no se puede deshacer.`)) {
            const payload = { public_id_usuario: idToDelete };
            const apiUrl = '/admin/colaboradores/delete';

            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error al eliminar:', error);
                alert('Ocurrió un error de comunicación con el servidor.');
            });
        }
    });

    // ------------------------------------------------------------
// Verificación de duplicidad del nombre de usuario
// ------------------------------------------------------------
$('#add_username').on('blur', function() {
    const usernameNuevo = $(this).val().trim();

    if (!usernameNuevo) return; // No hacer nada si está vacío

    fetch('/admin/colaboradores/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: usernameNuevo, verificar: true })
    })
    .then(response => response.json())
    .then(data => {
        if (data.existe) {
            showResultPopup(
                'Usuario duplicado',
                'El nombre de usuario ya está registrado. PORFISPORFISPORFIS, elija otro.',
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
    // Función para enviar form
    // ------------------------------------------------------------
    function enviarFormulario() {
        const payload = {
            nombres: $('#add_nombres').val(),
            apellido_p: $('#add_apellido_p').val(),
            apellido_m: $('#add_apellido_m').val(),
            username: $('#add_username').val(),
            password: $('#add_password').val(),
            rol: $('#add_rol').val(),
            public_id_privada: $('#add_privada').val(),
            estatus: $('#add_estatus').val(),
            correos: [],
            telefonos: []
        };

        if ($('#add_email1').val()) payload.correos.push($('#add_email1').val());
        if ($('#add_email2').val()) payload.correos.push($('#add_email2').val());
        if ($('#add_phone1').val()) payload.telefonos.push($('#add_phone1').val());
        if ($('#add_phone2').val()) payload.telefonos.push($('#add_phone2').val());

        fetch('/admin/colaboradores/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(async data => {
            if (data.success) {
                showResultPopup('¡Éxito!', 'Colaborador creado exitosamente!', 'success');
                hideAddModal();
                await new Promise(resolve => setTimeout(resolve, 1500));
                location.reload();
            } else {
                showResultPopup('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error al enviar datos:', error);
            showResultPopup('Error de Conexión', 'No se puede comunicar con la base de datos. Por favor, inténtelo más tarde.', 'error');
        });
    }

    // ------------------------------------------------------------
    // Validación del formulario antes de enviar
    // ------------------------------------------------------------
    addForm.on('submit', function(e) {
        e.preventDefault();
        let esValido = true;

        addForm.find('input[required], select[required]').each(function() {
            if ($(this).val() === "" || $(this).val() === null) {
                esValido = false;
                $(this).css('border-color', 'red'); 
            } else {
                $(this).css('border-color', ''); 
            }
        });

        if (!esValido) {
            showResultPopup('Error de Validación', 'Por favor, complete todos los campos obligatorios.', 'error');
            return;
        }

        const phoneRegex = /^[0-9]{10}$/;
        const phone1 = $('#add_phone1').val();
        const phone2 = $('#add_phone2').val();
        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            showResultPopup('Error de Formato', 'Cada número telefónico debe tener exactamente 10 dígitos numéricos.', 'error');
            return;
        }

        const password = $('#add_password').val();
        const validPassword =
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[0-9]/.test(password);

        if (!validPassword) {
            $('#password_message').text("Contraseña inválida. Revise los requisitos.").css("color", "red");
            showResultPopup('Error de Contraseña', 'Por favor, asegúrese de que la contraseña cumpla todos los requisitos.', 'error');
            return;
        }

        // CORRECCIÓN: ahora el formulario SÍ SE ENVÍA
        enviarFormulario();
    });

    // ------------------------------------------------------------
    // Validador de contraseña en tiempo real
    // ------------------------------------------------------------
    const passwordInput = document.getElementById("add_password");
    const passwordMessage = document.getElementById("password_message");

    passwordInput.addEventListener("input", function () {
        const password = passwordInput.value;
        const errors = [];

        if (password.length < 8) errors.push("Debe tener al menos 8 caracteres");
        if (!/[A-Z]/.test(password)) errors.push("Debe incluir al menos una letra mayúscula");
        if (!/[0-9]/.test(password)) errors.push("Debe incluir al menos un número");

        if (errors.length > 0) {
            passwordMessage.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px;">
                    ${iconoError}
                    <span style="color:red;">${errors.join(" | ")}</span>
                </div>`;
        } else {
            passwordMessage.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px;">
                    ${iconoCorrecto}
                    <span style="color:green;">Contraseña válida</span>
                </div>`;
        }
    });

    // ------------------------------------------------------------
    // Validación en tiempo real de números
    // ------------------------------------------------------------
    function validarTelefonoEnTiempoReal(inputId, messageId) {
        const phoneInput = document.getElementById(inputId);
        const message = document.getElementById(messageId);
        const phoneRegex = /^[0-9]{10}$/;

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

    validarTelefonoEnTiempoReal("add_phone1", "phone1_message");
    validarTelefonoEnTiempoReal("add_phone2", "phone2_message");
    validarTelefonoEnTiempoReal("inputTelefono", "mensajeTelefonoExistente");
    validarTelefonoEnTiempoReal("newTelefono", "mensajeTelefonoNuevo");

 

});