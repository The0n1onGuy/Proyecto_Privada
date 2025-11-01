<?php
// Función para generar las opciones de privadas
function generarOpcionesPrivadas($privadas) {
    $html = '<div class="privadas-dropdown-options" style="max-height:0; overflow:hidden; transition:max-height 0.3s ease;">';
    
    foreach ($privadas as $privada) {
        $nombreArchivo = str_replace(' ', '_', strtolower($privada['nombre'])) . '.jpg';
        $rutaImagen = "/images/privadas/" . $nombreArchivo;

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $rutaImagen)) {
            $rutaImagen = "/images/default_privada.png";
        }

        $html .= '
            <label style="display:flex; align-items:center; padding:8px 12px; cursor:pointer;" for="privada-'.$privada['id_privada'].'" class="privada-option">
                <img src="'.htmlspecialchars($rutaImagen).'" alt="'.htmlspecialchars($privada['nombre']).'" style="width:40px;height:40px;object-fit:cover;border-radius:6px;margin-right:10px;">
                <span>'.htmlspecialchars($privada['nombre']).'</span>
                <input type="radio" id="privada-'.$privada['id_privada'].'" name="id_privada" value="'.$privada['id_privada'].'" hidden>
            </label>
        ';
    }

    $html .= '</div>';
    return $html;
}
?>