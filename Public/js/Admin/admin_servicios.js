$(document).ready(function() {
    
    // Referencias al nuevo modal
    const serviceModal = $('#serviceModal');
    
    // --- ABRIR MODAL DE VISTA ---
    $('#tablaServicios tbody').on('click', '.btn-view', function() {
        const publicId = $(this).data('id');
        const btn = $(this);
        
        // btn.prop('disabled', true);

        fetch(`/admin/api/servicio/${publicId}`)
            .then(response => response.json())
            .then(data => {
                btn.prop('disabled', false);
                if (data.success) {
                    populateServiceModal(data.data);
                    serviceModal.addClass('visible');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.prop('disabled', false);
                alert('Error de conexión.');
            });
    });

    function populateServiceModal(data) {
        // $('#serviceId').val(data.public_id);
        $('#nombre_empresa').val(data.nombre_empresa);
        $('#nombre_encargado').val(data.nombre_encargado);
        $('#nom_serv').val(data.nom_serv);
        $('#categoria').val(data.categoria || 'Sin categoría'); 
        $('#precio_base').val(data.precio_base);
        $('#fecha_asignacion').val(data.fecha_asignacion);
        $('#edit_estatus').val(data.id_estatus);

        const phonesDiv = $('#lista_telefonos');
        phonesDiv.empty();

        if (data.telefonos && data.telefonos.length > 0) {
            data.telefonos.forEach(tel => {
                phonesDiv.append(`<span class="badge-contact phone">${tel.telefono}</span>`);
            });
        } else {
            phonesDiv.html('<span class="text-muted">Sin teléfonos registrados</span>');
        }

        // Llenar listas de contacto (Correos)
        const emailsDiv = $('#lista_correos');
        emailsDiv.empty();
        if (data.correos && data.correos.length > 0) {
            data.correos.forEach(mail => {
                emailsDiv.append(`<span class="badge-contact email">${mail.correo}</span>`);
            });
        } else {
            emailsDiv.append('<span class="text-muted">Sin correos registrados</span>');
        }
    }

    function hideServiceModal() {
        serviceModal.removeClass('visible');
    }
    $('#closeServiceModalBtn, #cancelServiceBtn').on('click', hideServiceModal);
    
});