<?php

// 1. Cargar Entorno y Clases
require __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno (asumiendo que Database.php las usa)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Core\Database;
use App\Models\SessionDataModel;
use App\Core\SessionVerifier;

// 2. Iniciar Sesión (¡Esencial para usar $_SESSION!)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Instanciar Clases
// Asumimos que Database::getConnection() funciona gracias al .env
try {
    $model = new SessionDataModel();
    $verifier = new SessionVerifier($model);
} catch (\PDOException $e) {
    echo "Error FATAL: No se pudo conectar a la BD. Verifica tu .env y Config/database.php";
    die();
}


/**
 * Función auxiliar para imprimir resultados de forma clara.
 */
function runTest(SessionVerifier $verifier, array $keysToVerify, string $testName)
{
    echo "=================================================<br>";
    echo "INICIO PRUEBA: $testName<br>";
    echo "-------------------------------------------------<br>";

    $isValid = $verifier->verify($keysToVerify);
    $errors = $verifier->getErrors();

    echo "Datos de Sesión Actuales: <pre>" . print_r($_SESSION, true) . "</pre>";
    echo "Claves a Verificar: <pre>" . print_r($keysToVerify, true) . "</pre>";
    echo "<b>Resultado de la Verificación: " . ($isValid ? 'EXITOSO (true)' : 'FALLIDO (false)') . "</b><br>";

    if (!empty($errors)) {
        echo "Errores encontrados:<br>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
    echo "FIN PRUEBA: $testName<br>";
    echo "=================================================<br><br>";
}

/**
 * Limpia la sesión actual para preparar la siguiente prueba.
 */
function resetSession()
{
    session_unset(); // Limpia todas las variables de sesión
    // NO usamos session_destroy() aquí porque queremos mantener
    // la misma sesión activa para el script, solo limpiando datos.
}

// === INICIO DE LOS ESCENARIOS DE PRUEBA ===

$keysToTest = ['user_id', 'id_privada', 'id_info'];

// ---
// Escenario 1: El "Happy Path" - Todos los datos son válidos
// ---
resetSession();
$_SESSION['user_id'] = 1;     // Asume que el ID 1 existe en priv_usuarios
$_SESSION['id_privada'] = 1;  // Asume que el ID 1 existe en priv_privadas
$_SESSION['id_info'] = 3;     // Asume que el ID 1 existe en priv_infousuario
runTest($verifier, $keysToTest, "Escenario 1: Datos Válidos y Coherentes");

// ---
// Escenario 2: Falla de Existencia (Clave Faltante)
// ---
resetSession();
$_SESSION['user_id'] = 1;
$_SESSION['id_privada'] = 1;
// $_SESSION['id_info'] no se define
runTest($verifier, $keysToTest, "Escenario 2: Clave 'id_info' Faltante");

// ---
// Escenario 3: Falla de "Vacío" (Clave vacía o nula)
// ---
resetSession();
$_SESSION['user_id'] = 1;
$_SESSION['id_privada'] = null; // Falla por 'empty()'
$_SESSION['id_info'] = 3;
runTest($verifier, $keysToTest, "Escenario 3: Clave 'id_privada' Nula/Vacía");

// ---
// Escenario 4: Falla de Coherencia de BD (ID no existe en BD)
// ---
resetSession();
$_SESSION['user_id'] = 99999; // Asume que el ID 99999 NO existe
$_SESSION['id_privada'] = 1;
$_SESSION['id_info'] = 1;
runTest($verifier, $keysToTest, "Escenario 4: Clave 'user_id' Incoherente con BD");

// ---
// Escenario 5: Caso Especial - Valor '0' (Debería pasar si 0 es un ID válido en BD)
// ---
resetSession();
$_SESSION['user_id'] = 0;     // Asume que el ID 0 existe en priv_usuarios
$_SESSION['id_privada'] = 1;
$_SESSION['id_info'] = 1;
runTest($verifier, $keysToTest, "Escenario 5: Clave 'user_id' es '0' (Válido si existe en BD)");

// ---
// Escenario 6: Clave extra que no se verifica en BD (Debería pasar)
// ---
resetSession();
$_SESSION['user_id'] = 1;
$_SESSION['id_privada'] = 1;
$_SESSION['id_info'] = 1;
$_SESSION['username'] = 'test'; // Esta clave pasa el 'default' en checkConsistency
runTest($verifier, ['user_id', 'username'], "Escenario 6: Clave ('username') no requiere chequeo BD");

// ---
// Escenario 7: Clave extra vacía (Debería fallar por 'empty')
// ---
resetSession();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = ''; // Esta clave fallará en la verificación 'empty'
runTest($verifier, ['user_id', 'username'], "Escenario 7: Clave ('username') está vacía");