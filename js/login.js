const textAU = document.getElementById('textAU');
const textAC = document.getElementById('textAC');
const Contra = document.getElementById('campoContra');

alternarVisLab(); //Ejecuta 1 vez, para que siempre esten ocultas
function AlternarVisContras(){
    if (Contra.type === "password") {
    Contra.type = "text";

  } else {
    Contra.type = "password";
}}

function alternarVisLab(){
    let x = textAU;
    let y = textAC;
    if (x.style.visibility  === "hidden") {
        x.style.visibility = "visible";
    } else {
        x.style.visibility = "hidden";
    }
    
    if (y.style.visibility === "hidden") {
        y.style.visibility = "visible";
    } else {
        y.style.visibility = "hidden";
    }
}
function RevisarYDir(){

    window.location.href = "paginas/pagina.php";
}

document.getElementById('btn').addEventListener('click', () => {
    window.location.href = "paginas/pagina.php";
});
