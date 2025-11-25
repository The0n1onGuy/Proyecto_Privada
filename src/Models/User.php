<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public function verifyCredentials($username, $password) {
        $conn = Database::getConnection();

        // Consulta SQL con un JOIN para obtener el nombre del rol
        $query = "SELECT 
                        u.contrasenia, 
                        u.id_rol, 
                        u.public_id AS user_public_id, 
                        p.public_id AS privada_public_id,
                        r.rol 
                  FROM priv_usuarios u 
                  JOIN priv_roles r ON u.id_rol = r.id_rol
                  LEFT JOIN priv_privadas p ON u.id_privada = p.id_privada
                  WHERE u.usuario = :username";
        $stmt = $conn->prepare($query);
        // $stmt = $conn->prepare(query: "SELECT u.*, r.rol FROM priv_usuarios u JOIN priv_roles r ON u.id_rol = r.id_rol WHERE u.usuario = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['contrasenia'])) {
            return $user;
        }
        return false;
    }
}