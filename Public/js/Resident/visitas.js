document.querySelectorAll('.toggle-detalle').forEach(btn => {
    btn.addEventListener('click', () => {
        const detalle = btn.closest('.visita-card').querySelector('.visita-detalle');
        const abierto = detalle.style.display === 'block';
        detalle.style.display = abierto ? 'none' : 'block';
        btn.textContent = abierto ? '▼' : '▲';
    });
});

document.getElementById('btnAgregarVisita').addEventListener('click', () => {
    const form = document.getElementById('formNuevaVisita');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
});
