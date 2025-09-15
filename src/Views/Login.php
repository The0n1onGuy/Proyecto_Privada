<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Privada Residencial - Inico</title>
  <link href="estilos/login.css" rel="stylesheet">
  <link href="estilos/info.css" rel="stylesheet"> <!--Contiene pop outs  -->  
</head>
<body>
  <div class="centerC">
    <div class="container">
        <h1 class="title">Bienvenido a la Privada Residencial</h1>  
          <h3 class="subtitle">Inicio de Sesión</h3>
          <div class="contenedor">
          <form><!--Sugerencia: Puedes centrar todo por centerC-->
            <label for="Usuario">Usuario
              <input id="campoUsuario" type="text">
              <label id= "textAU">Por favor, ingrese su usuario.</label>           
            </label>        
            <label for="Contraseña">Contraseña
              <!-- Una pequeña modificacion de input
              type text a password para que "censure" la contraseña y tambien algo en el CSS que lo modifica-->
              <input id ="campoContra" type="password">
              <button type="button" id="notabtn" onclick="AlternarVisContras()"> <!-- Arreglo temporal de estilo ya que la img es grande,limitalo como gustes -->
                <img style="width: 14px; height: 14px;" id = "" src="assets\images\show.svg" alt="mostrar/ocultar contraseña">
              </button>
              <label id= "textAC"> Por favor, ingrese su contraseña.</label>
            </label>
          </form>
                <button id="open-button">Popout alert</button>              
                <button               
                  id="btn" onclick="RevisarYDir()" type="button" class="btn" >
                  Iniciar Sesión
                </button>
            </div>
      </div>
  </div>
<div class="popout-box">
  <h2>ALERTA</h2>
  <p>Inicio de sesion exitoso.....</p>
  <button id="close-button">Close</button>
</div>
<script src="js/login.js"> </script>
</body>
</html>