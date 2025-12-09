$(document).ready(function() { 

    // Referencias a los elementos del DOM utilizados en el flujo principal
    const addModal = $('#addProveedorModal');    // Modal para agregar colaborador
    const addForm = $('#addProveedorForm');      // Formulario de creación de colaborador

function enviarFormulario() {
    const payload = {
        nombres: $('#add_nombreemp').val(),
        apellido_p: $('#add_nombreenc').val(),
        estatus: $('#add_estatus').val(),
        correos: [],
        telefonos: []
    };
    if ($('#add_email1').val()) payload.correos.push($('#add_email1').val());
    if ($('#add_email2').val()) payload.correos.push($('#add_email2').val());
    if ($('#add_phone1').val()) payload.telefonos.push($('#add_phone1').val());
    if ($('#add_phone2').val()) payload.telefonos.push($('#add_phone2').val());

    fetch('/admin/proveedores/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(async data => {
        if (data.success) {
            showResultPopup('¡Éxito!', 'Proveedor creado exitosamente!', 'success');
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

        
        enviarFormulario();
    });
});