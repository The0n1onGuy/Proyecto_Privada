$(document).ready(function() { 
    const addModal = $('#addCollaboratorModal');
    const addForm = $('#addCollaboratorForm');
    const form = $('#collaboratorForm');    
    
    $('#addCollaboratorBtn').on('click', function() {
        //Limpia cualquier dato posterior a la escritura y muestra el modal
        form[0].reset(); 
        addForm.find('input, select').css('border-color', ''); 
        addModal.addClass('visible');
    });
    
    function EscondeModal() {
        addModal.removeClass('visible');
    }
     $('#closeAddModalBtn, #cancelAddBtn').on('click', EscondeModal);
    
    
    //Boton de eliminar
    $('#tablaColaboradores tbody').on('click', '.btn-delete', function() {
        const button = $(this);
        const idToDelete = button.data('id');
        const row = button.closest('tr');
        const nombreCompleto = row.find('td:eq(1)').text() + ' ' + row.find('td:eq(2)').text();

        if (confirm(`¿Estás seguro de que quieres eliminar a ${nombreCompleto}? Esta acción no se puede deshacer.`)) {
            
            const payload = {
                id_usuario: idToDelete
            };

            console.log('Enviando solicitud para eliminar:', payload);
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


    // 3. Handle the form submission
    addForm.on('submit', function(e) {
        e.preventDefault(); // Prevent the default browser form submission

        //Validacion de cliente
        let esValido = true;
        //Busca dentro de la variable campos required
        addForm.find('input[required], select[required]').each(function() {
            if ($(this).val() === "" || $(this).val() === null) {
                esValido = false;
                //Sobre salta el borde del campo select o input que sea invalido con un color
                //Previene espacios vacios o selecciones no validas
                $(this).css('border-color', 'red'); 
            } else {
                $(this).css('border-color', ''); //Si el campo, no resaltes el borde
            }
        });
        //Si no es valido, manda un mensaje 
        if (!esValido) {
            alert('Por favor, complete todos los campos obligatorios.');
            return;
        }

        // 🔧 Aquí se agregará la validación de teléfonos
        // -------------------------------------------------
        // Verifica que los números telefónicos tengan exactamente 10 dígitos 
        // y que no contengan letras ni caracteres especiales.
        const phone1 = $('#add_phone1').val();
        const phone2 = $('#add_phone2').val();

        const phoneRegex = /^[0-9]{10}$/; // Solo 10 dígitos numéricos

        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            alert('Cada número telefónico debe tener exactamente 10 dígitos numéricos (sin letras ni símbolos).');
            return; // Detiene el envío del formulario si no cumple
        }
        // -------------------------------------------------

        // ✅ VALIDACIÓN DE CONTRASEÑA ANTES DE ENVIAR
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
            return; // 🚫 No deja continuar
        }
        // -------------------------------------------------

        // Obteniendo datos del los campos en el formulario
        const payload = {
            //Datos personales para tabla infousuario
            nombres: $('#add_nombres').val(),
            apellido_p: $('#add_apellido_p').val(),
            apellido_m: $('#add_apellido_m').val(),
            
            //Datos credenciales para tabla usuario
            username: $('#add_username').val(),
            password: $('#add_password').val(),

            ////Datos de asignacion para tabla usuario
            rol: $('#add_rol').val(),
            privada: $('#add_privada').val(),
            estatus: $('#add_estatus').val(),

            //Declara arrays para datos de contacto para tabla infousuario
            correos: [],
            telefonos: []
        };
        
        //Obten los 2 valores dentro de correos
        if ($('#add_email1').val()) {
            payload.correos.push($('#add_email1').val());
        }
        if ($('#add_email2').val()) {
            payload.correos.push($('#add_email2').val());
        }

        //Obten los 2 valores dentro de telefonos
        if ($('#add_phone1').val()) {
            payload.telefonos.push($('#add_phone1').val());
        }
        if ($('#add_phone2').val()) {
            payload.telefonos.push($('#add_phone2').val());
        }
        
        // ---PARA PRUEBAS: revisa en consola lo que se envia
        console.log("Payload for NEW collaborator:", JSON.stringify(payload, null, 2));
        // ---------------------------------------------------------------------

        // At this point, you would send the 'payload' to the server with fetch()
        // For example:
        
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
                location.reload(); // Or update the table dynamically
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
        
        
        alert("Data is ready to be sent! Check the browser's console (F12) to see the payload.");

    });



/*
    Sección de formulario donde el usuario ingresará su contraseña.
    Aquí se aplicará la validación de seguridad con las reglas establecidas:
    - Mínimo 8 caracteres
    - Al menos una letra mayúscula
    - Al menos un número
    - Al menos un carácter especial
*/
    
  const passwordInput = document.getElementById("add_password");
  const message = document.getElementById("password_message");
  const forms = document.getElementById("addCollaboratorForm");

  passwordInput.addEventListener("input", function () {
    const password = passwordInput.value;
    const errors = [];

    // Verificar longitud mínima de 8 caracteres
    if (password.length < 8) {
      errors.push("Debe tener al menos 8 caracteres");
    }

    // Verificar al menos una mayúscula
    if (!/[A-Z]/.test(password)) {
      errors.push("Debe incluir al menos una letra mayúscula");
    }

    // Verificar al menos un número
    if (!/[0-9]/.test(password)) {
      errors.push("Debe incluir al menos un número");
    }

    // Verificar al menos un carácter especial
    if (!/[!@#$%^&*(),.?":{}|<>_\-]/.test(password)) {
      errors.push("Debe incluir al menos un carácter especial");
    }

    // Mostrar los mensajes de error
    if (errors.length > 0) {
      message.textContent = "❌ " + errors.join(" | ");
      message.style.color = "red";
    } else {
      message.textContent = "✅ Contraseña válida";
      message.style.color = "green";
    }
  });

  // 🔒 Se mantiene este validador extra por seguridad
  forms.addEventListener("submit", function (event) {
    const password = passwordInput.value;
    const valid =
      password.length >= 8 &&
      /[A-Z]/.test(password) &&
      /[0-9]/.test(password) &&
      /[!@#$%^&*(),.?":{}|<>_\-]/.test(password);

    if (!valid) {
      event.preventDefault();
      alert("Por favor, asegúrese de que la contraseña cumpla todos los requisitos antes de continuar.");
    }
  });



});    
