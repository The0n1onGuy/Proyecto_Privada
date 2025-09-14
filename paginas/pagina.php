<?php 
 require_once "../clases/WhatsApp.php" ;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TU MAMA - Inico</title>
  <link href="../estilos/pagina.css" rel="stylesheet">
  
</head>

<body class ="center">
  <div class="container">

      <h1 class="title">Contactanos</h1><br>
          
          <div class="contenedor">
           
             <button id="butn" type="submit" class="btn" > <a href="<?php echo $Starter-> getwhatsApplink("Gay");?>" target="_blank"><img class="mi-imagen" src="../estilos/WhatsApp.jpg" alt="Una ilustración colorida"></a></button></br>
          </div>

            

    </div>
    
<script src="js/pagina.js"> </script>
</body>

</html>

