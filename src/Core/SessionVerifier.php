<?php

namespace App\Core;

use App\Models\SessionDataModel;

class SessionVerifier
{
    private $model;
    private $errors = []; // Para almacenar mensajes de error (opcional)

    /**
     * Constructor que recibe una instancia del modelo.
     * @param SessionDataModel $model Instancia del modelo para verificaciones en BD.
     */
    public function __construct(SessionDataModel $model)
    {
        $this->model = $model;
    }

    /**
     * Verifica un conjunto de claves de sesión.
     * Comprueba existencia, si no están vacías y coherencia con la BD.
     *
     * @param array $keysToCheck Un array de strings con las claves de sesión a verificar (ej. ['user_id', 'id_privada']).
     * @return bool True si todas las verificaciones pasan, False si alguna falla.
     */
    public function verify(array $keysToCheck): bool
    {
        $this->errors = []; // Limpia errores previos

        foreach ($keysToCheck as $key) {
            // 1. Verificar Existencia
            if (!array_key_exists($key, $_SESSION)) {
                $this->errors[] = "La clave de sesión '{$key}' no existe.";
                continue; // Puedes cambiar a `return false;` si quieres fallar inmediatamente
            }

            // 2. Verificar que no esté vacío (considera si 0 o '0' son válidos)
            // Adaptar esta lógica si el valor 0 es permitido para alguna clave.
            if (empty($_SESSION[$key]) && $_SESSION[$key] !== 0 && $_SESSION[$key] !== '0') {
                 $this->errors[] = "La clave de sesión '{$key}' está vacía.";
                 continue; // O `return false;`
            }

            // 3. Verificar Coherencia con la Base de Datos (si aplica)
            // El modelo determinará si esta clave necesita verificación en BD
            if (!$this->model->checkConsistency($key, $_SESSION[$key])) {
                $this->errors[] = "El valor de la sesión '{$key}' ('{$_SESSION[$key]}') no es coherente con la base de datos.";
                continue; // O `return false;`
            }
        }

        // Si el array de errores está vacío, todas las verificaciones pasaron
        return empty($this->errors);
    }

    /**
     * Obtiene los mensajes de error de la última verificación (opcional).
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}