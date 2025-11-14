
    // --- FORMULARIO RESIDENTE EXTRA ---
    const addExtraModal = $('#addExtraModal');
    const addExtraForm = $('#addExtraForm');

    $('#addExtraBtn').on('click', function() {
        addExtraForm[0].reset();
        addExtraForm.find('input, select').css('border-color', '');
        addExtraModal.addClass('visible');
    });

    function hideAddExtraModal() { addExtraModal.removeClass('visible'); }
    $('#closeExtraModalBtn, #cancelExtraBtn').on('click', hideAddExtraModal);
    
    const numCasaInput = $('#add_extra_num_casa');
const propietarioLabel = $('#add_extra_propietario');

numCasaInput.on('blur', function() {
    const numCasa = $(this).val().trim();

    if (!numCasa) {
        propietarioLabel.val(''); // o .text('') si usas un span
        return;
    }

    fetch('/admin/residentes/createEX', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ num_casa: numCasa, soloPropietario: true })
})

    .then(response => response.json())
    .then(data => {
        propietarioLabel.val(data.success ? data.propietario : ''); // solo visual
    })
    .catch(err => {
        console.error('Error al consultar propietario:', err);
        propietarioLabel.val('');
    });
});


    addExtraForm.on('submit', function(e) {
        e.preventDefault();

        // Validar teléfonos
        const phone1 = $('#addExtraForm #extra_add_phone1').val().trim();
        const phone2 = $('#addExtraForm #extra_add_phone2').val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if ((phone1 && !phoneRegex.test(phone1)) || (phone2 && !phoneRegex.test(phone2))) {
            showResultPopup('Error', 'Cada número telefónico debe tener exactamente 10 dígitos numéricos.', 'error');
            return;
        }

        // Crear payload
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

        fetch('/admin/residentes/createEX', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            hideAddExtraModal();
            if (data.success) {
    showResultPopup('¡Éxito!', 'Residente extra creado exitosamente.', 'success');

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