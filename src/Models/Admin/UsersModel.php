<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
// Obtener todos los usuarios
class UsersModel {

    /**
    * Obtiene la información principal de todos los usuarios PROPIETARIOS.
    * Une la información de la persona, su usuario de login y su rol.
    *
    * @return array
    */
    public function getUsers() {
        try {
            $conn = Database::getConnection();
            
            // Esta consulta es la clave. Une la información de varias tablas.
            $sql = "
                SELECT 
                    u.id_usuario,
                    u.usuario,
                    iu.id_info,
                    iu.nombres,
                    iu.apellido_p,
                    iu.apellido_m,
                    tu.telefono,
                    cu.correo,
                    r.rol,
                    e.estatus
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                JOIN priv_telusuario tu ON iu.id_info = tu.id_info
                JOIN priv_corresusuario cu ON iu.id_info = cu.id_info
                JOIN priv_roles r ON u.id_rol = r.id_rol
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                WHERE iu.es_propietario = 1
                ORDER BY iu.id_info ASC
            ";
            
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se registraría el error.
            return [];
        }
    }
}