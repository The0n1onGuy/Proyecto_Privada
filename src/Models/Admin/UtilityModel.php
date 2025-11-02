<?php

namespace App\Models\Admin;
use App\Core\Database;
use PDO;

class UtilityModel {

    public function obtenTodosPrivadas(){

        // Obtiene todas las privadas que están habilitadas "id_estatus = 1"

        $conn = Database::getConnection();
        $stmt = $conn->query("SELECT id_privada, nombre FROM priv_privadas WHERE id_estatus = '1'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenDatosEstatus(){

        // Obtiene todas las privadas que están habilitadas "id_estatus = 1"

        $conn = Database::getConnection();
        $stmt = $conn->query("SELECT estatus FROM priv_estatus");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenPrimDatosEstatus(){
        $conn = Database::getConnection();
        $sql = "SELECT estatus FROM priv_estatus WHERE id_estatus IN (?, ?)";
        $stmt = $conn->prepare($sql);
        // Ejecutamos la consulta pasando los IDs indirectamente por execute 
        $stmt->execute([1, 2]);
        // (FETCH_COLUMN dará un array simple respecto a la columna en este caso debe ser: ['Activo', 'Inactivo'])
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    public function obtenDatosColabRoles(){
        $conn = Database::getConnection();
        $sql = "SELECT rol FROM priv_roles WHERE id_rol IN (?, ?, ?,?)";
        $stmt = $conn->prepare($sql);
        // Ejecutamos la consulta pasando los IDs indirectamente por execute 
        $stmt->execute([1, 3, 4,5]);
        
        // (FETCH_COLUMN dará un array simple respecto a la columna)
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    /**
     * Verifica si un nombre de usuario ya existe en la base de datos.
     *
     * @param string $username Nombre de usuario a verificar
     * @return bool Retorna true si existe, false si no existe
     */
    public function verificarUsuario($username) {
        $conn = Database::getConnection(); // Usando la clase Database

        $query = "SELECT COUNT(*) as total FROM priv_usuarios WHERE usuario = :usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':usuario', $username);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado['total'] > 0;
    }

}