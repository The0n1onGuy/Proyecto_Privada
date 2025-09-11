<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>PH Login</title>
    <link rel="stylesheet" href="assets/styles/app.css" />
    <script src = "/assets/scripts/app.js"></script>
</head>
<body class="centerC">
    <div class="col-md-4">
        <form class="row g-3 needs-validation p-4 border rounded-3 bg-white shadow-sm" novalidate>
            <h2 class="text-center mb-3">Iniciar Sesión</h2>

            <div class="col-12">
                <label for="validationCustom01" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="validationCustom01" required>
                <div class="invalid-feedback">
                    Por favor, ingrese su usuario.
                </div>
            </div>
             
            <div class="col-12">
              <div class="row g-3 align-items-left">
                <div class="col-auto">
                <label for="validationCustom05" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="validationCustom05" required>
                <div class="invalid-feedback">
                    Por favor, ingrese su contraseña.
                </div>
                </div>
                <!-- Espacio reservado paraun boton de vista alterna de contraseña -->
                <div class="col-auto">
                  <button class="botonI" type="submit">Imaginate un ojo</button>
                </div>
                <!-------------->    
              </div>
            </div>
            
            
            

            <div class="col-12">
                <button class="btn btn-primary w-100" type="submit">Iniciar Sesión</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>