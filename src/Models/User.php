<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public function verifyCredentials($username, $password) {
        $conn = Database::getConnection();

        // Consulta SQL con un JOIN para obtener el nombre del rol
        $stmt = $conn->prepare(query: "SELECT u.*, r.rol FROM priv_usuarios u JOIN priv_roles r ON u.id_rol = r.id_rol WHERE u.usuario = :username");
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