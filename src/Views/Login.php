<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Privada Residencial - Inico</title>
  <link href="css/login.css" rel="stylesheet">
  <link href="css/info.css" rel="stylesheet">
</head>
<body>
  <div class="centerC">
    <div class="container">
        <h1 class="title">Bienvenido a la Privada Residencial</h1>  
          <h3 class="subtitle">Inicio de Sesión</h3>
          <div class="contenedor">
          <form>
            <label for="Usuario">Usuario
              <input id="campoUsuario" type="text">
              <label id= "textAU">Por favor, ingrese su usuario.</label>           
            </label>        
            <label for="Contraseña">Contraseña
              <input id ="campoContra" type="password">
              <button type="button" id="notabtn" onclick="AlternarVisContras()">
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
<script src="js/login.js"> </script>
</body>
</html>