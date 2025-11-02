<?php
    // --- CAMBIO ---
    // Importamos la nueva clase de Seguridad al inicio del archivo
    use App\Core\Security;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    </head>
<body>
    <div class="main-container">
        <form action="/admin/set-private" method="post" class="form-privadas">
            <div class="privadas-container">
                <?php foreach ($privadas as $privada): ?>

                    <?php
                        // ... (Tu lógica de imágenes sigue igual) ...
                        $nombreArchivo = str_replace(' ', '_', strtolower($privada['nombre'])) . '.jpg';
                        $rutaImagen = "/images/privadas/" . $nombreArchivo;
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $rutaImagen)) {
                            $rutaImagen = "/images/default_privada.png";
                        }
                    ?>

                    <input type="radio" 
                           id="privada-<?php echo $privada['id_privada']; ?>" 
                           name="id_privada" 
                           
                           value="<?php echo Security::encryptId($privada['id_privada']); ?>" 
                           
                           hidden>

                    <label for="privada-<?php echo $privada['id_privada']; ?>" class="privada-card">
                        </label>

                <?php endforeach; ?>
            </div>
            </form>
    </div>
    </body>
</html>