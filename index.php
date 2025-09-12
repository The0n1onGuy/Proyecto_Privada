<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PH Login</title>
    <link rel="stylesheet" href="assets/styles/app.css" />
    <script src = "/assets/scripts/app.js"></script>
</head>
<body class="centerC">
    <form class="Ccontainer">
        <h2 class="centerT">Iniciar Sesión</h2>
        <label for="validationCustom01" class="form-label">Usuario</label>
        <input type="text" class="form-control" id="validationCustom01" required>
        <div class="invalid-feedback">
            Por favor, ingrese su usuario.
        </div>

        <label for="validationCustom05" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="validationCustom05" required>
        <div class="invalid-feedback">
            Por favor, ingrese su contraseña.
        </div>
        <!-- Espacio reservado para un boton de vista alterna de contraseña -->
        <button >
                <img class="botonI" id="toggleIcon" src="assets\images\hide.svg" alt="\Proyecto_Privada\assets\images\show.svg">
        </button>
        
        <div>
            <button class="botonI" type="submit" id = "Toggle_button">Imaginate un ojo</button>
        </div>
        <!-------------->    
        
        <button class="btn btn-primary w-100" type="submit">Iniciar Sesión</button>
    </form>
</body>
</html>