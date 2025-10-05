$(document).ready(function() {
    const modal = $('#collaboratorModal');
    const form = $('#collaboratorForm');
    const modalTitle = $('#modalTitle');
    const collaboratorIdInput = $('#collaboratorId');

    // --- 1. Show the Modal for a NEW Collaborator ---
    $('#addCollaboratorBtn').on('click', function() {
        form[0].reset(); // Clear any previous data from the form
        collaboratorIdInput.val(''); // Ensure the hidden ID is empty
        modalTitle.text('Añadir Nuevo Colaborador');
        modal.addClass('visible');
    });

    // --- 2. Show the Modal to EDIT a Collaborator ---
    // We use a delegated event listener on the table body to handle clicks on
    // buttons that are added dynamically by DataTables.
    $('#tablaColaboradores tbody').on('click', '.btn-edit', function() {
        // In a real app, you would fetch the full collaborator data via AJAX.
        // For this layout example, we'll use placeholder data.
        const placeholderData = {
            id: $(this).data('id'),
            nombre: 'Carlos Mendoza (Fetched)',
            rol: 'Supervisor',
            privada: 'Residencial Las Palmas',
            estatus: 'Activo'
        };

        // Populate the form with the fetched data
        collaboratorIdInput.val(placeholderData.id);
        $('#nombre').val(placeholderData.nombre);
        $('#rol').val(placeholderData.rol);
        $('#privada').val(placeholderData.privada);
        $('#estatus').val(placeholderData.estatus);
        
        modalTitle.text('Editar Colaborador');
        modal.addClass('visible');
    });

    // --- 3. Handle the DELETE Action ---
    $('#tablaColaboradores tbody').on('click', '.btn-delete', function() {
        const idToDelete = $(this).data('id');
        // In a real app, you would show a confirmation dialog first.
        if (confirm(`¿Estás seguro de que quieres eliminar al colaborador con ID ${idToDelete}?`)) {
            // Here you would make an AJAX call to your server to delete the record.
            console.log('Deleting collaborator with ID:', idToDelete);
            // On success, you would tell the DataTable to reload.
            // $('#tablaColaboradores').DataTable().ajax.reload();
        }
    });

    // --- 4. Hide the Modal ---
    function hideModal() {
        modal.removeClass('visible');
    }

    // Hide when clicking the close button or cancel button
    $('#closeModalBtn, #cancelBtn').on('click', hideModal);

    // Hide when clicking outside the modal content
    modal.on('click', function(e) {
        if ($(e.target).is(modal)) {
            hideModal();
        }
    });

    // --- 5. Handle Form Submission ---
    form.on('submit', function(e) {
        e.preventDefault();

        const formData = {
            id_usuario: collaboratorIdInput.val(),
            nombres: $('#nombre').val(),
            correo: $('#correo').val(),
            telefono: $('#telefono').val(),
            privada: $('#privada').val(),
            rol: $('#rol').val(),
            estatus: $('#estatus').val()
        };

        const isUpdate = !!formData.id_usuario;
        
        // ==========================================================
        // == THE FIX IS HERE: Use the new router URL            ==
        // ==========================================================
        const apiUrl = isUpdate ? '/admin/colaboradores/update' : '/admin/colaboradores/create'; // Example for create

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                hideModal();
                location.reload(); // Simple page reload for now
            } else {
                alert('Error: ' . data.message);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Ocurrió un error de comunicación con el servidor.');
        });
    });
    if (document.getElementById('tablaColaboradores')) {
        $('#tablaColaboradores').DataTable({
            "language": { "url": "/js/spanish.json" },
            "order": [[0, "desc"]],
            "paging":   false,      // Disables pagination (Next/Previous buttons)
            "searching": false,     // Disables the search box
            "info":     false,      // Hides "Showing 1 of X entries"
            "lengthChange": false, // Hides the "Show X entries" dropdown
            "scrollY":  "200px",    // Enables vertical scrolling with a fixed height
            "scrollCollapse": true // Makes the table smaller if there are few rows
        });
    }
});