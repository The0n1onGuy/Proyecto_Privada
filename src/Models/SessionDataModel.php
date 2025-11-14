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
        // Usaremos un array para los parámetros
        $params = []; 
 
        try {
            switch ($key) {
                case 'user_id':
                    // Usamos '?' para consistencia
                    $sql = "SELECT 1 FROM priv_usuarios WHERE id_usuario = ? LIMIT 1";
                    $params = [$value];
                    break;
 
                case 'public_id_privada':
                    // Esta consulta ya estaba bien
                    $sql = "SELECT 1 FROM priv_privadas WHERE public_id = ? AND id_estatus = '1' LIMIT 1";
                    $params = [$value];
                    break;
 
                case 'id_info':
                    // Usamos '?' para consistencia
                    $sql = "SELECT 1 FROM priv_infousuario WHERE id_info = ? LIMIT 1";
                    $params = [$value];
                    break;
                    
                case 'public_id_usuario':
                    $sql = "SELECT 1 FROM priv_usuarios WHERE public_id = ? LIMIT 1";
                    $params = [$value];
                    break;

                default:
                    return true;
            }
 
            $stmt = $this->conn->prepare($sql);
            
            // --- ¡AQUÍ ESTÁ LA CORRECCIÓN! ---
            // Pasamos el array de parámetros directamente a execute().
            // Ya no usamos bindParam y no forzamos el tipo INT.
            $stmt->execute($params);
 
            return $stmt->fetchColumn() !== false;
 
        } catch (PDOException $e) {
            error_log("Error en checkConsistency para key '{$key}': " . $e->getMessage());
            return false;
        }
    }
}