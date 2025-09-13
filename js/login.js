const textAU = document.getElementById('textAU');
const textAC = document.getElementById('textAC');
const botonVis = document.getElementById('butonI');

alternarVisLab();
function OcultaAlertas(){
    textAC.classList.toggle('hidden');
}

function alternarVisLab(){
    let x = textAU;
    let y = textAC;
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
    if (y.style.display === "none") {
        y.style.display = "block";
    } else {
        y.style.display = "none";
    }
}
function RevisarYDir(){
    window.location.href = "paginas/pagina.php";
}

document.getElementById('btn').addEventListener('click', () => {
    window.location.href = "../paginas/pagina.php";
});
