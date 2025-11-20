<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use Exception;
// Obtener todos los usuarios
class ColaboradoresModel {
    private const ESTATUS_ACTIVO = 1;
    private const ESTATUS_INACTIVO = 2;
    /**
    * Obtiene la información principal de todos los usuarios PROPIETARIOS.
    * Une la información de la persona, su usuario de login y su rol.
    * @return array
    */
    //Funcion consulta para mostrar en tabla
    public function obtenColaboradores(string $public_id_privada, array $roles_permitidos) {
        try {
            $conn = Database::getConnection();
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

            // Crear la consulta SQL usando los placeholders dinámicos
            $sql = "
            SELECT DISTINCT
                iu.id_info, u.id_usuario, u.public_id, iu.nombres, iu.apellido_p, iu.apellido_m,
                r.rol, e.estatus, pv.nombre AS privada_nombre
            FROM priv_usuarios u
            JOIN priv_infousuario iu ON u.id_usuario = iu.id_usuario
            JOIN priv_roles r ON u.id_rol = r.id_rol
            JOIN priv_estatus e ON u.id_estatus = e.id_estatus
            
            -- Unimos la tabla de privadas para poder filtrar por el UUID
            JOIN priv_privadas pv ON u.id_privada = pv.id_privada 
            
            -- Filtramos usando el 'public_id' (UUID), no el 'id_privada'
            WHERE pv.public_id = :public_id 
              AND r.rol IN ($in_placeholders) 
            ORDER BY iu.id_info ASC
            ";
            
            $stmt = $conn->prepare($sql);
            
            // --- CAMBIO EN LOS PARÁMETROS ---
            // El parámetro principal ahora es el string UUID
            $params = [':public_id' => $public_id_privada];
            
            // Luego, añade todos los parámetros de rol (ej. ':rol0' => 'Administrador')
            foreach ($roles_permitidos as $key => $role) {
                $params[":rol" . $key] = $role;
            }

            // Ejecutar con todos los parámetros
            $stmt->execute($params);
            
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

            // --- GESTIONAR CORREOS BASADO EN LA ACCIÓN ---
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
                    case 'delete':
                        $sql = "UPDATE priv_corresusuario SET id_estatus = ? WHERE id_correo = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([self::ESTATUS_INACTIVO, $data['correo_id']]);
                        break;
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
                    case 'delete':
                        $sql = "UPDATE priv_telusuario SET id_estatus = ? WHERE id_telefono = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([self::ESTATUS_INACTIVO, $data['telefono_id']]);
                        break;
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
                    $conn->prepare($sql)->execute([$id_info, $data['telefono_nuevo'],self::ESTATUS_ACTIVO]);
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
            // Crear la cuenta de usuario en `priv_usuarios` ---
            $sqlUser = "INSERT INTO priv_usuarios (usuario, contrasenia, id_estatus, id_rol, id_privada, public_id)
                        VALUES (
                            :username, 
                            :password,
                            (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus), 
                            (SELECT id_rol FROM priv_roles WHERE rol = :rol),
                            (SELECT id_privada FROM priv_privadas WHERE public_id = :public_id_privada),
                            UUID() 
                        )";
            
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':estatus' => $data['estatus'],
                ':rol' => $data['rol'],
                ':public_id_privada' => $data['public_id_privada']
            ]);
            
            // Obtenemos el ID del usuario que acabamos de crear
            $id_usuario = $conn->lastInsertId();

            // Insertar la información personal en `priv_infousuario`
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

            // Insertar los correos en `priv_corresusuario` 
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, ?)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) {
                    $stmtMail->execute([$id_info, $correo,self::ESTATUS_ACTIVO]);
                }
            }

            // Insertar los teléfonos en `priv_telusuario` 
            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, ?)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) {
                    $stmtPhone->execute([$id_info, $telefono,self::ESTATUS_ACTIVO]);
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
    public function eliminarColaborador(string $public_id_usuario){ //REMPLAZO PARCIAL RECUERDA CAMBIAR LA REFERENCIA EN EL JAAVSCRIPT 1:45PM
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // TRADUCIR EL UUID A ID NUMÉRICO ---
            $stmtId = $conn->prepare("SELECT id_usuario FROM priv_usuarios WHERE public_id = ?");
            $stmtId->execute([$public_id_usuario]);
            $id_usuario_numerico = $stmtId->fetchColumn();

            if (!$id_usuario_numerico) {
                throw new Exception("Colaborador no encontrado con ese ID público.");
            }

            // USAR EL ID NUMÉRICO INTERNAMENTE ---
            
            // (Esta consulta usa el $id_usuario_numerico)
            $stmtInfo = $conn->prepare("SELECT id_info FROM priv_infousuario WHERE id_usuario = ?");
            $stmtInfo->execute([$id_usuario_numerico]);
            $id_info = $stmtInfo->fetchColumn();

            if ($id_info) {
                // (Estas consultas usan el $id_info, que depende del numérico)
                $stmtMail = $conn->prepare("UPDATE priv_corresusuario SET id_estatus = ? WHERE id_info = ?");
                $stmtMail->execute([self::ESTATUS_INACTIVO, $id_info]);
            }

            // (Esta consulta usa el $id_usuario_numerico)
            $stmtUserUpdate = $conn->prepare("UPDATE priv_usuarios SET id_estatus = ? WHERE id_usuario = ?");
            $stmtUserUpdate->execute([self::ESTATUS_INACTIVO, $id_usuario_numerico]);
            
            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
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
}