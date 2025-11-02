$(document).ready(function() { 
    const addModal = $('#addCollaboratorModal');
    const addForm = $('#addCollaboratorForm');
    const form = $('#collaboratorForm');    
    
    $('#addCollaboratorBtn').on('click', function() {
        form[0].reset(); 
        addForm.find('input, select').css('border-color', ''); 
        addModal.addClass('visible');
    });
    
    function EscondeModal() {
        addModal.removeClass('visible');
    }
    $('#closeAddModalBtn, #cancelAddBtn').on('click', EscondeModal);
    
    // Botón de eliminar
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

    // Función para enviar el formulario al servidor
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
                alert('Colaborador creado exitosamente!');
                EscondeModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error al enviar datos:', error);
            alert('No se puede comunicar con la base de datos. Por favor, inténtelo más tarde.');
        });
    }

    // Manejo del submit con validaciones
    addForm.on('submit', function(e) {
        e.preventDefault();
        // Validación de campos requeridos
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
            alert('Por favor, complete todos los campos obligatorios.');
            return;
        }

        // Validación de teléfonos
        const phone1 = $('#add_phone1').val();
        const phone2 = $('#add_phone2').val();
        const phoneRegex = /^[0-9]{10}$/;
        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            alert('Cada número telefónico debe tener exactamente 10 dígitos numéricos (sin letras ni símbolos).');
            return;
        }

        // Validación de contraseña
        const password = $('#add_password').val();
        const passwordMessage = $('#password_message');
        const validPassword =
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[0-9]/.test(password) &&
            /[!@#$%^&*(),.?":{}|<>_\-]/.test(password);

        if (!validPassword) {
            alert("Por favor, asegúrese de que la contraseña cumpla todos los requisitos antes de continuar.");
            passwordMessage.text("❌ Contraseña inválida. Revise los requisitos.").css("color", "red");
            return;
        }
        enviarFormulario();
        // // Verificación del username en la base de datos
        // const username = $('#add_username').val();
        // if (!username) {
        //     alert('Por favor, ingrese un nombre de usuario.');
        //     return;
        // }

        // fetch('/Models/Admin/verificar_usuario.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ username: username })
        // })
        // .then(response => response.json())
        // .then(data => {
        //     if (data.success) {
        //         // Usuario válido, puede enviar formulario
        //         enviarFormulario();
        //     } else {
        //         alert('Error: ' + data.message);
        //     }
        // })
        // .catch(error => {
        //     console.error('Error de comunicación con la base de datos:', error);
        //     alert('No se puede comunicar con la base de datos. Por favor, inténtelo otra vez');
        // });
    });

    // Validador de contraseña en tiempo real
    const passwordInput = document.getElementById("add_password");
    const message = document.getElementById("password_message");
    passwordInput.addEventListener("input", function () {
        const password = passwordInput.value;
        const errors = [];

        if (password.length < 8) errors.push("Debe tener al menos 8 caracteres");
        if (!/[A-Z]/.test(password)) errors.push("Debe incluir al menos una letra mayúscula");
        if (!/[0-9]/.test(password)) errors.push("Debe incluir al menos un número");
        if (!/[!@#$%^&*(),.?":{}|<>_\-]/.test(password)) errors.push("Debe incluir al menos un carácter especial");

        if (errors.length > 0) {
            message.textContent = "❌ " + errors.join(" | ");
            message.style.color = "red";
        } else {
            message.textContent = "✅ Contraseña válida";
            message.style.color = "green";
        }
    });

});
