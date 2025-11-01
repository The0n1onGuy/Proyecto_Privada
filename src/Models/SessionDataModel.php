<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

class SessionDataModel
{
    private $conn;

    /**
     * Constructor para obtener la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Verifica si un valor específico para una clave de sesión dada
     * existe en la tabla correspondiente de la base de datos.
     *
     * @param string $key La clave de sesión (ej. 'id_usuario', 'id_privada', 'id_info').
     * @param mixed $value El valor de la sesión a verificar.
     * @return bool True si el valor existe en la BD, False en caso contrario.
     */
    public function checkConsistency(string $key, $value): bool
    {
        $sql = "";
        $paramValue = $value; // Usamos una variable separada para el bindParam

        try {
            switch ($key) {
                case 'user_id': // Asumiendo que usas 'user_id' en la sesión como en LoginController
                    $sql = "SELECT 1 FROM priv_usuarios WHERE id_usuario = :value LIMIT 1";
                    break;
                case 'id_privada':
                    $sql = "SELECT 1 FROM priv_privadas WHERE id_privada = :value LIMIT 1";
                    break;
                case 'id_info':
                     // Asegúrate de que la tabla y columna sean correctas
                    $sql = "SELECT 1 FROM priv_infousuario WHERE id_info = :value LIMIT 1";
                    break;
                // Puedes añadir más casos si necesitas verificar otras claves de sesión contra la BD
                default:
                    // Si la clave no es una que requiera verificación en BD, asumimos que es válida
                    // o podrías retornar false si se quiere ser más estricto.
                    return true;
            }

            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':value', $paramValue, PDO::PARAM_INT);
            $stmt->execute();

            // fetchColumn() devuelve el valor de la primera columna (el '1') o false si no hay filas
            return $stmt->fetchColumn() !== false;

        } catch (PDOException $e) {
            // En un entorno real, registrarías este error
            error_log("Error en checkConsistency para key '{$key}': " . $e->getMessage());
            return false; // Ante un error de BD, consideramos la verificación fallida
        }
    }
}