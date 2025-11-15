
(function() {

    document.querySelectorAll('.toggle-detalle').forEach(btn => {
        btn.addEventListener('click', () => {
            const detalle = btn.closest('.visita-card').querySelector('.visita-detalle');
            const abierto = detalle.style.display === 'block';
            detalle.style.display = abierto ? 'none' : 'block';
            btn.textContent = abierto ? '▼' : '▲';
        });
    });


    const btnAgregarVisita = document.getElementById('btnAgregarVisita');
    const formContainer = document.getElementById('formNuevaVisita');
    const form = formContainer ? formContainer.querySelector('form') : null;

    if (btnAgregarVisita && formContainer) {
        btnAgregarVisita.addEventListener('click', () => {
            formContainer.style.display = formContainer.style.display === 'block' ? 'none' : 'block';
        });
    }


    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.textContent = 'Guardando...';
            submitButton.disabled = true;

            fetch('/resident/visitas/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showGlobalPopup('Visita Agregada', data.message || 'La visita fue registrada correctamente.', 'Entendido', 'success');
                    form.reset();
                    formContainer.style.display = 'none';
                    if (window.loadSection) {
                        window.loadSection('visitas');
                    }
                } else {
                    showGlobalPopup('Error al Agregar Visita', data.message || 'No se pudo registrar la visita.', 'Entendido', 'error');
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                showGlobalPopup('Error de Red', 'Ocurrió un problema al comunicarse con el servidor.', 'Entendido', 'error');
            })
            .finally(() => {
                submitButton.textContent = 'Guardar';
                submitButton.disabled = false;
            });
        });
    }

    
    document.querySelectorAll('.form-estatus').forEach(formEstatus => {
        formEstatus.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(formEstatus);
            const button = formEstatus.querySelector('button[type="submit"]');
            button.textContent = 'Actualizando...';
            button.disabled = true;

            fetch('/resident/visitas/update-status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showGlobalPopup('Estatus Actualizado', data.message || 'El estado de la visita fue actualizado correctamente.', 'Entendido', 'success');
                    if (window.loadSection) {
                        window.loadSection('visitas');
                    }
                } else {
                    showGlobalPopup('Error al Actualizar', data.message || 'No se pudo actualizar el estatus.', 'Entendido', 'error');
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                showGlobalPopup('Error de Red', 'No se pudo conectar con el servidor.', 'Entendido', 'error');
            })
            .finally(() => {
                button.textContent = 'Actualizar';
                button.disabled = false;
            });
        });
    });

})();