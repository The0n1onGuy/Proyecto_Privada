<?php 
 require_once "clases/WhatsApp.php" ;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Privada Residencial - Inico</title>
  <link href="estilos/login.css" rel="stylesheet">
  <link href="estilos/login.js" rel="stylesheet">
</head>

<body class ="centerC">
  <div class="container">

      <h1 class="title">Bienvenido a la Privada Residencial</h1>  
          <h3 class="subtitle">Inicio de Sesión</h3>
          
          <div class="contenedor">
              <form><!--Sugerencia: Puedes centrar todo por centerC-->
                  
                  <label for="Usuario">Usuario</br>
                    <input type="text"></br> 
                    <label id= "textAU"> Por favor, ingrese su usuario.</label></br>                 
                  </label>
                  
                  <label for="Contraseña">Contraseña</br> 
                    <!-- Una pequeña modificacion de input
                    type text a password para que "censure" la contraseña y tambien algo en el CSS que lo modifica-->
                    <input type="password"> 
                    <button type="button" id="notabtn" onclick="alternarVisLab()"> <!-- Arreglo temporal de estilo ya que la img es grande,limitalo como gustes -->
                      <img style="width: 14px; height: 14px;" src="assets\images\show.svg" alt="mostrar/ocultar contraseña">
                    </button></br> 
                    <label id= "textAC"> Por favor, ingrese su contraseña.  </br></label>
                  </label>
                  <button id="btn" onclick="RevisarYDir()" type="submit" class="btn" >Iniciar Sesión</button></br>
              </form>
          </div>
    </div>
    
<script src="js/login.js"> </script>
</body>

</html>


