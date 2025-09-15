const textAU = document.getElementById('textAU'); //"AU" = alerta usuario
const textAC = document.getElementById('textAC'); // "AC" = alerta contraseña
const popoutBox = document.querySelector('.popout-box'); 
let Contra = document.getElementById('campoContra');
let Usuario = document.getElementById('campoUsuario');
let UsuText, ConText;

VisibiLab(1,"hidden"); //Ejecutan unica vez, para que siempre esten ocultas al refrescar
VisibiLab(2,"hidden");
function RevisarYDir(){ 
    UsuText = Usuario.value.trim();
    ConText = Contra.value.trim();
    if (UsuText === "") {        
        VisibiLab(1,"visible");
        return;
    } else{
        VisibiLab(1,"hidden");
    } 
    if (ConText === ""){
        VisibiLab(2,"visible");
        return;
    }else{
        VisibiLab(2,"hidden");
    }
    popoutBox.classList.add('active'); 
    setTimeout(function() {
        popoutBox.classList.remove('active');
    }, 2000); // (3 segundos) El tiempo que le toma en redireccionandolo
    setTimeout(function() {
        window.location.href = "paginas/pagina.php";
    }, 3000);
}

function VisibiLab(tipoCam,status){
    let tipo;
    switch (tipoCam){
        case 1: 
            tipo = textAU
            break;
        case 2:
            tipo = textAC
            break;
    }
    tipo.style.visibility = status
}

function AlternarVisContras(){
    if (Contra.type === "password") {
    Contra.type = "text";

  } else {
    Contra.type = "password";
}}

//  Referncia de julio
//document.getElementById('btn').addEventListener('click', () => {
//     window.location.href = "paginas/pagina.php";
// });

