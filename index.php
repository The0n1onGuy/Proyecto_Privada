<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PH Login</title>
    <link rel="stylesheet" href="assets/styles/app.css" />
    <link rel="stylesheet" href="assets/styles/textF.css" />
    <link rel="stylesheet" href="assets/styles/info.css" />
    <script src = "/assets/scripts/app.js"></script>
</head>
<body class="centerC">
    <form class="flexible">
        <div class="Ccontainer">
            <h2 class="centerT" style="padding-top: 20px;" >Iniciar Sesión</h2>
            <label for="validationCustom01" class="form-label">Usuario</label><br/>
            <div class="alerttxt">
                <label id= "alerttxt"> Por favor, ingrese su usuario.</label>
            </div>
            <input type="text" class="form-control" id="validationCustom01" required><br/>        

            <label for="validationCustom05" class="form-label">Contraseña</label><br/>
            <div class="alerttxt">
                <label id= "alerttxt"> Por favor, ingrese su contraseña. </label>
            </div>
            <div class="passwordC">
                <input type="password" class="form-control" id="validationCustom05" required>
                <button>
                    <img class="botonI" id="toggleIcon" src="assets\images\show.svg" alt="mostrar/ocultar contraseña">
                </button>
            </div>
            <!-- <div class="centerT">
                <button class="btn btn-primary w-100" type="submit">Iniciar Sesión</button>
            </div> -->
            <button class="centerT" type="submit">Iniciar Sesión</button>
        </div>
    </form>
</body>
</html>