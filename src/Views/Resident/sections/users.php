<?php
// Imprimimos el mensaje del modelo que nos pasó el controlador.
if (isset($usersMessage)) {
    echo "<p><strong>Mensaje del Modelo:</strong> " . htmlspecialchars($usersMessage) . "</p>";
}
echo "<p><strong>Mensaje del Segmento:</strong> ola residente, sección users</p>";
?>