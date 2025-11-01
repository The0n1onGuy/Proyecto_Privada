<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
// Obtener todos los usuarios
class ColaboradoresModelBCK {

    // --- CAMBIO: Constantes de estatus definidas ---
    private const ESTATUS_ACTIVO = 1;
    private const ESTATUS_INACTIVO = 2;
    // ----------------------------------------------

    /**
    * Obtiene la información principal de todos los usuarios PROPIETARIOS.
    * Une la información de la persona, su usuario de login y su rol.
    * @return array
    */
    //Funcion consulta para mostrar en tabla
    public function obtenColaboradores($id_privada, array $roles_permitidos) {
        try {
            $conn = Database::getConnection();
            
            // --- INICIO DE LA MODIFICACIÓN ---

            // Si por alguna razón el array de roles está vacío, no devuelvas nada.
            if (empty($roles_permitidos)) {
                return [];
            }

            //Crear nombres placeholders nombrados para la cláusula IN (como. :rol0, :rol1, :rol2) 
            //ya que el tamaño de la tabla puede variar
            $role_placeholders = [];
            foreach ($roles_permitidos as $key => $role) {
                $role_placeholders[] = ":rol" . $key;
            }
            // Esto crea un string como ":rol0,:rol1,:rol2" por medio del implode 
            // donde toma cada elemento del array y los une con un caracter escojido ','
            $in_placeholders = implode(',', $role_placeholders);

            // 2. Crear la consulta SQL usando los placeholders dinámicos
            $sql = "
            SELECT DISTINCT
                iu.id_info,
                u.id_usuario,
                iu.nombres,
                iu.apellido_p,
                iu.apellido_m,
                r.rol,
                e.estatus,
                pv.nombre AS privada_nombre
            FROM priv_usuarios u
            JOIN priv_infousuario iu ON u.id_usuario = iu.id_usuario
            JOIN priv_roles r ON u.id_rol = r.id_rol
            JOIN priv_estatus e ON u.id_estatus = e.id_estatus
            LEFT JOIN priv_privadas pv ON u.id_privada = pv.id_privada 
            WHERE u.id_privada = :id_privada 
              AND r.rol IN ($in_placeholders) 
            ORDER BY iu.id_info ASC
            ";
            
            $stmt = $conn->prepare($sql);
            
            // 3. Crear el array de parámetros para execute()
            // Primero, añade el parámetro :id_privada
            $params = [':id_privada' => $id_privada];
            
            // Luego, añade todos los parámetros de rol (ej. ':rol0' => 'Administrador')
            foreach ($roles_permitidos as $key => $role) {
                $params[":rol" . $key] = $role;
            }

            // 4. Ejecutar con todos los parámetros
            $stmt->execute($params);
            
            // --- FIN DE LA MODIFICACIÓN ---
            
            $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fija todos los telefonos y correos ligados al id_usuario
            foreach ($colaboradores as $key => $colaborador) {
                $id_usuario = $colaborador['id_usuario'];
                $colaboradores[$key]['correos'] = $this->getCorreosByUsuarioId($id_usuario);
                $colaboradores[$key]['telefonos'] = $this->getTelefonosByUsuarioId($id_usuario);
            }

            return $colaboradores;

        } catch (\PDOException $e) { // Capturar la excepción específica de PDO
            //Registraría el errores.
            error_log("Error en ObtenColaboradores: " . $e->getMessage());
            return [];
        }
    }

    //Funcion de crear colaboradores --- AUN EN PROCESO DEJALE LAS QUERIES A VELA
    public function actualizaColaborador(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // ---ACTUALIZAR DATOS PRINCIPALES DEL COLABORADOR ---
            $sqlUser = "UPDATE priv_usuarios SET 
                            id_rol = (SELECT id_rol FROM priv_roles WHERE rol = :rol), 
                            id_estatus = (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus),
                            id_privada = (SELECT id_privada FROM priv_privadas WHERE nombre = :privada)
                        WHERE id_usuario = :id_usuario";
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':rol' => $data['rol'],
                ':estatus' => $data['estatus'],
                ':privada' => $data['privada'],
                ':id_usuario' => $data['id_usuario']
            ]);

            $sqlInfo = "UPDATE priv_infousuario SET 
                            nombres = :nombres, 
                            apellido_p = :apellido_p, 
                            apellido_m = :apellido_m
                        WHERE id_usuario = :id_usuario";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                ':nombres' => $data['nombres'],
                ':apellido_p' => $data['apellido_p'],
                ':apellido_m' => $data['apellido_m'],
                ':id_usuario' => $data['id_usuario']
            ]);

            // --- 2. GESTIONAR CORREOS BASADO EN LA ACCIÓN ---
            if (!empty($data['correo_id'])) {
                switch ($data['email_action']) {
                    case 'edit':
                        $sql = "UPDATE priv_corresusuario SET correo = :correo WHERE id_correo = :id_correo";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':correo' => $data['correo_actual'],
                            ':id_correo' => $data['correo_id']
                        ]);
                        break;
                    
                    // --- CAMBIO AQUÍ ---
                    case 'delete':
                        // En lugar de borrar, actualizamos el estatus a Inactivo
                        $sql = "UPDATE priv_corresusuario SET id_estatus = ? WHERE id_correo = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([self::ESTATUS_INACTIVO, $data['correo_id']]);
                        break;
                    // --- FIN DEL CAMBIO ---

                    // 'view' and other cases do nothing to existing contacts
                }
            }
            
            // Añadir nuevo correo si se proporcionó
            if (!empty($data['correo_nuevo'])) {
                $stmtInfoId = $conn->prepare("SELECT id_info FROM priv_infousuario WHERE id_usuario = ?");
                $stmtInfoId->execute([$data['id_usuario']]);
                $id_info = $stmtInfoId->fetchColumn();
                if ($id_info) {
                    $sql = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, ?)";
                    $conn->prepare($sql)->execute([$id_info, $data['correo_nuevo'], self::ESTATUS_ACTIVO]);
                }
            }

            // --- 3. GESTIONAR TELÉFONOS BASADO EN LA ACCIÓN ---
            if (!empty($data['telefono_id'])) {
                switch ($data['phone_action']) {
                    case 'edit':
                        $sql = "UPDATE priv_telusuario SET telefono = :telefono WHERE id_telefono = :id_telefono";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':telefono' => $data['telefono_actual'],
                            ':id_telefono' => $data['telefono_id']
                        ]);
                        break;
                    
                    // --- CAMBIO AQUÍ ---
                    case 'delete':
                        // En lugar de borrar, actualizamos el estatus a Inactivo
                        $sql = "UPDATE priv_telusuario SET id_estatus = ? WHERE id_telefono = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([self::ESTATUS_INACTIVO, $data['telefono_id']]);
                        break;
                    // --- FIN DEL CAMBIO ---
                }
            }
            
            // Añadir nuevo teléfono si se proporcionó
            if (!empty($data['telefono_nuevo'])) {
                if (empty($id_info)) { // Reuse id_info if available
                     $stmtInfoId = $conn->prepare("SELECT id_info FROM priv_infousuario WHERE id_usuario = ?");
                     $stmtInfoId->execute([$data['id_usuario']]);
                     $id_info = $stmtInfoId->fetchColumn();
                }
                if ($id_info) {
                    $sql = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, ?)";
                    $conn->prepare($sql)->execute([$id_info, $data['telefono_nuevo'], self::ESTATUS_ACTIVO]);
                }
            }
            
            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Actualizacion fallida!: " . $e->getMessage());
            // Lanza la excepción para que el controlador la maneje
            throw $e;
        }
    }
    public function creaColaborador(array $data){
        $conn = Database::getConnection();
        $conn->beginTransaction();
        
        try {
            // --- PASO 1: Crear la cuenta de usuario en `priv_usuarios` ---
            $sqlUser = "INSERT INTO priv_usuarios (usuario, contrasenia, id_estatus, id_rol, id_privada)
                        VALUES (
                            :username, 
                            :password,
                            (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus), 
                            (SELECT id_rol FROM priv_roles WHERE rol = :rol),
                            (SELECT id_privada FROM priv_privadas WHERE nombre = :privada)
                        )";
            
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $data['username'],
                // ¡IMPORTANTE! Hashear la contraseña antes de guardarla.
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':estatus' => $data['estatus'],
                ':rol' => $data['rol'],
                ':privada' => $data['privada']
            ]);
            
            // Obtenemos el ID del usuario que acabamos de crear
            $id_usuario = $conn->lastInsertId();

            // --- PASO 2: Insertar la información personal en `priv_infousuario` ---
            $sqlInfo = "INSERT INTO priv_infousuario (id_usuario, nombres, apellido_p, apellido_m, es_propietario)
                        VALUES (?, ?, ?, ?, ?)"; // 0 = no es propietario
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                $id_usuario,
                $data['nombres'],
                $data['apellido_p'],
                $data['apellido_m'],
                0
            ]);
            
            // Obtenemos el ID de la info que acabamos de crear para enlazar los contactos
            $id_info = $conn->lastInsertId();

            // --- PASO 3: Insertar los correos en `priv_corresusuario` ---
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, ?)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) {
                    // --- CAMBIO: Se usa la constante en lugar de '1' ---
                    $stmtMail->execute([$id_info, $correo, self::ESTATUS_ACTIVO]);
                }
            }

            // --- PASO 4: Insertar los teléfonos en `priv_telusuario` ---
            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, ?)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) {
                    // --- CAMBIO: Se usa la constante en lugar de '1' ---
                    $stmtPhone->execute([$id_info, $telefono, self::ESTATUS_ACTIVO]);
                }
            }

            // Si todas las consultas fueron exitosas, confirma los cambios.
            $conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falló, deshace todos los cambios.
            $conn->rollBack();
            // Lanza la excepción para que el controlador pueda manejarla.
            throw $e;
        }
    }
    /**
     * Elimina un colaborador y toda su información asociada de la base de datos.
     * @param int $id_usuario El ID del usuario a eliminar.
     * @return bool
     * @throws Exception
     */
    public function eliminarColaborador(int $id_usuario){
        $conn = Database::getConnection();
        // 1. Iniciar una transacción para asegurar la integridad de los datos.
        $conn->beginTransaction();

        // --- CAMBIO: Se elimina la variable local, usaremos la constante ---
        // $inactive_status_id = 2;

        try {
            // Primero, necesitamos el 'id_info' para poder desactivar los contactos.
            $stmtInfo = $conn->prepare("SELECT id_info FROM priv_infousuario WHERE id_usuario = ?");
            $stmtInfo->execute([$id_usuario]);
            $id_info = $stmtInfo->fetchColumn();

            if ($id_info) {
                // 2. Actualizar los correos asociados a "Inactivo".
                $stmtMail = $conn->prepare("UPDATE priv_corresusuario SET id_estatus = ? WHERE id_info = ?");
                // --- CAMBIO: Se usa la constante ---
                $stmtMail->execute([self::ESTATUS_INACTIVO, $id_info]);

                // 3. Actualizar los teléfonos asociados a "Inactivo".
                $stmtPhone = $conn->prepare("UPDATE priv_telusuario SET id_estatus = ? WHERE id_info = ?");
                // --- CAMBIO: Se usa la constante ---
                $stmtPhone->execute([self::ESTATUS_INACTIVO, $id_info]);
            }

            // 4. La tabla 'priv_infousuario' no se borra, simplemente se queda
            //    vinculada al 'id_usuario' que ahora está inactivo.

            // 5. Finalmente, actualizar la cuenta de usuario principal a "Inactivo".
            $stmtUserUpdate = $conn->prepare("UPDATE priv_usuarios SET id_estatus = ? WHERE id_usuario = ?");
            // --- CAMBIO: Se usa la constante ---
            $stmtUserUpdate->execute([self::ESTATUS_INACTIVO, $id_usuario]);
            
            // Si todo salió bien, confirma todos los cambios en la base de datos.
            $conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falló en cualquiera de los pasos, deshace TODOS los cambios.
            $conn->rollBack();
            // Lanza la excepción para que el controlador la maneje.
            throw $e;
        }
    }
    // public function eliminarColaborador(int $id_usuario){
    //     $conn = Database::getConnection();
    //     // 1. Iniciar una transacción para asegurar la integridad de los datos.
    //     $conn->beginTransaction();

    //     try {
    //         // Primero, necesitamos el 'id_info' para poder borrar los contactos.
    //         $stmtInfo = $conn->prepare("SELECT id_info FROM priv_infousuario WHERE id_usuario = ?");
    //         $stmtInfo->execute([$id_usuario]);
    //         $id_info = $stmtInfo->fetchColumn();

    //         if ($id_info) {
    //             // 2. Eliminar los correos asociados.
    //             $stmtMail = $conn->prepare("DELETE FROM priv_corresusuario WHERE id_info = ?");
    //             $stmtMail->execute([$id_info]);

    //             // 3. Eliminar los teléfonos asociados.
    //             $stmtPhone = $conn->prepare("DELETE FROM priv_telusuario WHERE id_info = ?");
    //             $stmtPhone->execute([$id_info]);
    //         }

    //         // 4. Eliminar el registro de información personal.
    //         $stmtInfoDelete = $conn->prepare("DELETE FROM priv_infousuario WHERE id_usuario = ?");
    //         $stmtInfoDelete->execute([$id_usuario]);

    //         // 5. Finalmente, eliminar la cuenta de usuario principal.
    //         $stmtUserDelete = $conn->prepare("DELETE FROM priv_usuarios WHERE id_usuario = ?");
    //         $stmtUserDelete->execute([$id_usuario]);
            
    //         // Si todo salió bien, confirma todos los cambios en la base de datos.
    //         $conn->commit();
    //         return true;

    //     } catch (Exception $e) {
    //         // Si algo falló en cualquiera de los pasos, deshace TODOS los cambios.
    //         $conn->rollBack();
    //         // Lanza la excepción para que el controlador la maneje.
    //         throw $e;
    //     }
    // }  
    /**
     * Obtiene todos los correos asociados a un id_usuario.
     * @param int $id_usuario
     * @return array
     */
    public function getCorreosByUsuarioId(int $id_usuario): array
    {
        $conn = Database::getConnection();
        $sql = "SELECT c.id_correo, c.correo 
                FROM priv_corresusuario c
                JOIN priv_infousuario i ON c.id_info = i.id_info
                WHERE i.id_usuario = ? AND c.id_estatus = ?"; 
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_usuario, self::ESTATUS_ACTIVO]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los teléfonos asociados a un id_usuario.
     * @param int $id_usuario
     * @return array
     */
    public function getTelefonosByUsuarioId(int $id_usuario): array
    {
        $conn = Database::getConnection();
        $sql = "SELECT t.id_telefono, t.telefono 
                FROM priv_telusuario t
                JOIN priv_infousuario i ON t.id_info = i.id_info
                WHERE i.id_usuario = ? AND t.id_estatus = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_usuario, self::ESTATUS_ACTIVO]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
