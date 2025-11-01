$(document).ready(function() {

    // MENU INTERNO: mostrar solo la sección activa
    const menuButtons = document.querySelectorAll('.menu-btn');
    const sectionContainers = document.querySelectorAll('.section-container');
    menuButtons.forEach(btn=>{
        btn.addEventListener('click', ()=>{
            menuButtons.forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            const targetSection = btn.getAttribute('data-section');
            sectionContainers.forEach(sec=>{
                sec.classList.toggle('active', sec.id===targetSection);
            });
        });
    });

    // BOTONES DE SECCIÓN: mostrar solo panel correspondiente
    document.querySelectorAll('.config-btn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const target = btn.getAttribute('data-target');
            const allPanels = document.querySelectorAll('.option-panel');
            allPanels.forEach(p=>{ if(p.id!==target) p.style.display='none'; });
            const panel = document.getElementById(target);
            panel.style.display = panel.style.display==='flex' ? 'none' : 'flex';
            panel.style.flexDirection='column';
        });
    });

    // PERSONALIZACIÓN
    const themeToggle = document.getElementById('themeToggle');
    const fontSizeRange = document.getElementById('fontSizeRange');
    const colorPicker = document.getElementById('colorPicker');
    const backgroundInput = document.getElementById('backgroundInput');
    const backgroundPreview = document.getElementById('backgroundPreview');

    themeToggle.addEventListener('change', ()=>document.body.classList.toggle('dark-mode', themeToggle.checked));
    fontSizeRange.addEventListener('input', ()=>document.body.style.fontSize = fontSizeRange.value+'px');
    colorPicker.addEventListener('input', ()=> {
        document.documentElement.style.setProperty('--color-card', colorPicker.value+'15');
        document.querySelectorAll('.section-title').forEach(el=>el.style.borderLeftColor=colorPicker.value);
    });
    backgroundInput.addEventListener('change', (event)=>{
        const file = event.target.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = e=>{
                document.body.style.backgroundImage = `url(${e.target.result})`;
                backgroundPreview.style.backgroundImage = `url(${e.target.result})`;
                backgroundPreview.textContent='';
            };
            reader.readAsDataURL(file);
        }
    });

    // MODAL EDITAR PERFIL
    const editModal = document.getElementById('editModal');
    const openEditFormBtn = document.getElementById('openEditForm');
    
    // Busca los botones de cerrar por sus nuevos IDs
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');

    openEditFormBtn.addEventListener('click', () => {
        // Simplemente añade la clase '.visible' para mostrar el modal
        editModal.classList.add('visible');
    });

    // Función para cerrar el modal
    function hideModal() {
        editModal.classList.remove('visible');
    }

    // Asigna la función de cerrar a los botones
    closeModalBtn.addEventListener('click', hideModal);
    cancelModalBtn.addEventListener('click', hideModal);

    // Cierra si se hace clic en el fondo oscuro
    window.addEventListener('click', e => {
        if (e.target === editModal) {
            hideModal();
        }
    });

}); // <-- CIERRA EL WRAPPER
// rwardRef

