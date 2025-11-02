$(document).ready(function() { 
    const addModal = $('#addCollaboratorModal');
    const addForm = $('#addCollaboratorForm');
    const form = $('#collaboratorForm');    
    
    // ------------------------------------------------------------
    // Mostrar modal de agregar colaborador
    // ------------------------------------------------------------
    $('#addCollaboratorBtn').on('click', function() {
        form[0].reset(); 
        addForm.find('input, select').css('border-color', ''); 
        addModal.addClass('visible');
    });

    function hideAddModal() {
        addModal.removeClass('visible');
    }

    $('#closeAddModalBtn, #cancelAddBtn').on('click', hideAddModal);
    $(document).on('keydown', function(e) { if (e.key === "Escape") hideAddModal(); });
    addModal.on('click', function(e) { if ($(e.target).is(addModal)) hideAddModal(); });

    // ------------------------------------------------------------
    // Botón de eliminar colaborador con popup
    // ------------------------------------------------------------
    $('#tablaColaboradores tbody').on('click', '.btn-delete', function() {
        const button = $(this);
        const idToDelete = button.data('id');
        const row = button.closest('tr');
        const nombreCompleto = row.find('td:eq(1)').text() + ' ' + row.find('td:eq(2)').text();

        if (confirm(`¿Estás seguro de que quieres eliminar a ${nombreCompleto}? Esta acción no se puede deshacer.`)) {
            const payload = { id_usuario: idToDelete };
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
    // Función para enviar formulario al servidor
    // ------------------------------------------------------------
    function enviarFormulario() {
        const payload = {
            nombres: $('#add_nombres').val(),
            apellido_p: $('#add_apellido_p').val(),
            apellido_m: $('#add_apellido_m').val(),
            username: $('#add_username').val(),
            password: $('#add_password').val(),
            rol: $('#add_rol').val(),
            privada: $('#add_privada').val(),
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
        .then(data => {
            if (data.success) {
                showResultPopup('¡Éxito!', 'Colaborador creado exitosamente!', 'success');
                hideAddModal();
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
    // Validaciones del formulario
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
            /[0-9]/.test(password) &&
            /[!@#$%^&*(),.?":{}|<>_\-]/.test(password);

        if (!validPassword) {
            $('#password_message').text("❌ Contraseña inválida. Revise los requisitos.").css("color", "red");
            showResultPopup('Error de Contraseña', 'Por favor, asegúrese de que la contraseña cumpla todos los requisitos.', 'error');
            return;
        }

        // ------------------------------------------------------------
        // Verificación de usuario duplicado antes de agregar
        // ------------------------------------------------------------
        const usernameNuevo = $('#add_username').val().trim();

       fetch('/admin/colaboradores/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: usernameNuevo, verificar: true })
    })

        .then(response => response.json())
        .then(data => {
            if (data.existe) {
                showResultPopup('Usuario duplicado', 'El nombre de usuario ya está registrado. Por favor, elija otro.', 'error');
                $('#add_username').css('border-color', 'red');
                return;
            } else {
                enviarFormulario();
            }
        })
        .catch(error => {
            console.error('Error al verificar usuario:', error);
            showResultPopup('Error de Conexión', 'No se pudo verificar el usuario. Inténtelo nuevamente.', 'error');
        });
        
    });

    // ------------------------------------------------------------
    // Validador de contraseña en tiempo real
    // ------------------------------------------------------------
    const passwordInput = document.getElementById("add_password");
    const message = document.getElementById("password_message");
    passwordInput.addEventListener("input", function () {
        const password = passwordInput.value;
        const errors = [];

        if (password.length < 8) errors.push("Debe tener al menos 8 caracteres");
        if (!/[A-Z]/.test(password)) errors.push("Debe incluir al menos una letra mayúscula");
        if (!/[0-9]/.test(password)) errors.push("Debe incluir al menos un número");
        if (!/[!@#$%^&*(),.?\":{}|<>_\\-]/.test(password)) errors.push("Debe incluir al menos un carácter especial");

        if (errors.length > 0) {
            message.textContent = "❌ " + errors.join(" | ");
            message.style.color = "red";
        } else {
            message.textContent = "✅ Contraseña válida";
            message.style.color = "green";
        }
    });
});