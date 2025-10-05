<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
// Obtener todos los usuarios
class ColaboradoresModel {

    /**
    * Obtiene la información principal de todos los usuarios PROPIETARIOS.
    * Une la información de la persona, su usuario de login y su rol.
    *
    * @return array
    */
    //Funcion consulta para mostrar en tabla
    public function getColaboradores() {
        try {
            $conn = Database::getConnection();
            
            // Esta consulta es la clave. Une la información de varias tablas.
            $sql = "
                SELECT 
                    u.id_usuario,
                    iu.nombres,
                    iu.apellido_p,
                    iu.apellido_m,
                    tu.telefono,
                    cu.correo,
                    r.rol,
                    e.estatus,
                    pv.nombre AS privada_nombre
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                LEFT JOIN priv_telusuario tu ON iu.id_info = tu.id_info
                LEFT JOIN priv_corresusuario cu ON iu.id_info = cu.id_info
                JOIN priv_roles r ON u.id_rol = r.id_rol
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                LEFT JOIN priv_privadas pv ON iu.id_privada = pv.id_privada 
                WHERE r.rol IN ('Colaborador', 'Seguridad', 'Supervisor','Administrador') 
                ORDER BY iu.id_info ASC
            ";
            
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se registraría el error.
            return [];
        }
    }
    //Funcion de actualizacion
    public function updateColaborador(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // --- PASO 1: Actualizar priv_usuarios (rol y estatus) ---
            $sqlUser = "UPDATE priv_usuarios SET 
                            id_rol = (SELECT id_rol FROM priv_roles WHERE rol = :rol), 
                            id_estatus = (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus) 
                        WHERE id_usuario = :id_usuario";
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':rol' => $data['rol'],
                ':estatus' => $data['estatus'],
                ':id_usuario' => $data['id_usuario']
            ]);

            // --- PASO 2: Actualizar priv_infousuario (nombres y privada) ---
            $nameParts = explode(' ', $data['nombres'], 3);
            $nombres = $nameParts[0] ?? '';
            $apellido_p = $nameParts[1] ?? '';
            $apellido_m = $nameParts[2] ?? '';
            
            $sqlInfo = "UPDATE priv_infousuario SET 
                            nombres = :nombres, 
                            apellido_p = :apellido_p, 
                            apellido_m = :apellido_m, 
                            id_privada = (SELECT id_privada FROM priv_privadas WHERE nombre = :privada) 
                        WHERE id_usuario = :id_usuario";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                ':nombres' => $nombres,
                ':apellido_p' => $apellido_p,
                ':apellido_m' => $apellido_m,
                ':privada' => $data['privada'],
                ':id_usuario' => $data['id_usuario']
            ]);
            
            // --- PASO 3: Actualizar priv_corresusuario (correo) ---
            $sqlMail = "UPDATE priv_corresusuario SET correo = :correo WHERE id_info = (SELECT id_info FROM priv_infousuario WHERE id_usuario = :id_usuario)";
            $stmtMail = $conn->prepare($sqlMail);
            $stmtMail->execute([
                ':correo' => $data['correo'], 
                ':id_usuario' => $data['id_usuario']
            ]);

            // --- PASO 4: Actualizar priv_telusuario (teléfono) ---
            $sqlPhone = "UPDATE priv_telusuario SET telefono = :telefono WHERE id_info = (SELECT id_info FROM priv_infousuario WHERE id_usuario = :id_usuario)";
            $stmtPhone = $conn->prepare($sqlPhone);
            $stmtPhone->execute([
                ':telefono' => $data['telefono'], 
                ':id_usuario' => $data['id_usuario']
            ]);

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            // Lanza la excepción para que el controlador la maneje
            throw $e;
        }
    }
    public function getAllPrivadas() {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT id_privada, nombre FROM priv_privadas WHERE id_estatus = 1 ORDER BY nombre ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}