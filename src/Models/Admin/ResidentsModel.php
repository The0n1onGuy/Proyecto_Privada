<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use Exception;
class ResidentsModel {

    /**
     * Obtiene los residentes de una privada específica, con un filtro opcional.
     *
     * @param int $id_privada El ID de la privada.
     * @param string $filterType El tipo de filtro ('owners' para solo propietarios, 'all' para todos).
     * @return array
     */
    public function getAllResidents(string $public_id_privada, $filterType = 'owners') {
        try {
            $conn = Database::getConnection();
            // Hacemos uso de GROUP CONCAT para  tomar todos los 
            // valores de una columna que pertenecen al mismo grupo 
            // (dentro de la misma query) y los une (concatena) en una sola cadena de texto.
            $sql = "
                SELECT
                    iu.id_info,
                    iu.id_usuario,
                    iu.nombres,
                    iu.apellido_p,
                    iu.apellido_m,
                    u.num_casa,
                    r.rol,
                    p.nombre AS privada_nombre,
                    e.estatus,
                    GROUP_CONCAT(DISTINCT ct.correo SEPARATOR ', ') AS correos,
                    GROUP_CONCAT(DISTINCT tt.telefono SEPARATOR ', ') AS telefonos,
                    iu.es_propietario
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario 
                JOIN priv_roles r ON u.id_rol = r.id_rol
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                JOIN priv_privadas p ON u.id_privada = p.id_privada
                LEFT JOIN priv_corresusuario ct ON iu.id_info = ct.id_info AND ct.id_estatus = 1
                LEFT JOIN priv_telusuario tt ON iu.id_info = tt.id_info AND tt.id_estatus = 1
                WHERE p.public_id = :public_id AND r.rol = 'Usuario' ";

            // Aplica el filtro si es para 'owners'
            if ($filterType === 'owners') {
                $sql .= " AND iu.es_propietario = 1";
            }
            // Si es 'all', no se añade ninguna condición extra, trayendo a todos.

            $sql .= "
                GROUP BY iu.id_info
                ORDER BY u.num_casa ASC, iu.es_propietario DESC, iu.id_info ASC
                ";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':public_id' => $public_id_privada]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // Manejo de errores
            error_log("Error al obtener los residentes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene la información detallada de un residente por su ID de información.
     *
     * @param int $id_info El ID de información del residente.
     * @return array|false
     */
    public function getResidentById($id_info) {
        try {
            $conn = Database::getConnection();
            
            // Obtener datos principales
            $sql_main = "
                SELECT 
                    iu.id_info, iu.nombres, iu.apellido_p, iu.apellido_m,
                    u.num_casa, p.nombre AS privada_nombre, e.estatus, iu.es_propietario
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                JOIN priv_privadas p ON u.id_privada = p.id_privada
                WHERE iu.id_info = :id_info";
            
            $stmt_main = $conn->prepare($sql_main);
            $stmt_main->execute([':id_info' => $id_info]);
            $resident = $stmt_main->fetch(PDO::FETCH_ASSOC);

            if (!$resident) {
                return false;
            }

            // Obtener correos
            $sql_emails = "SELECT id_correo, correo FROM priv_corresusuario WHERE id_info = :id_info";
            $stmt_emails = $conn->prepare($sql_emails);
            $stmt_emails->execute([':id_info' => $id_info]);
            $resident['correos'] = $stmt_emails->fetchAll(PDO::FETCH_ASSOC);

            // Obtener teléfonos - CORREGIDO
            $sql_phones = "SELECT id_telefono, telefono FROM priv_telusuario WHERE id_info = :id_info";
            $stmt_phones = $conn->prepare($sql_phones);
            $stmt_phones->execute([':id_info' => $id_info]);
            $resident['telefonos'] = $stmt_phones->fetchAll(PDO::FETCH_ASSOC);
            
            return $resident;

        } catch (\PDOException $e) {
            error_log("Error al obtener el residente por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza la información de un residente en la base de datos.
     *
     * @param array $data Datos del residente a actualizar.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizaResident(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // ... (Las actualizaciones de infousuario y usuarios se quedan igual) ...
            
            // 1. Actualizar priv_infousuario
            $nameParts = explode(' ', $data['nombreCompleto'], 3);
            $sqlInfo = "UPDATE priv_infousuario SET nombres = :nombres, apellido_p = :apellido_p, apellido_m = :apellido_m, es_propietario = :es_propietario WHERE id_info = :id_info";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                ':nombres' => $nameParts[0] ?? '',
                ':apellido_p' => $nameParts[1] ?? '',
                ':apellido_m' => $nameParts[2] ?? '',
                ':es_propietario' => $data['es_propietario'],
                ':id_info' => $data['id_info']
            ]);

            // 2. Actualizar priv_usuarios
            $sqlUser = "UPDATE priv_usuarios u JOIN priv_infousuario iu ON u.id_usuario = iu.id_usuario SET u.num_casa = :num_casa, u.id_estatus = (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus) WHERE iu.id_info = :id_info";
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':num_casa' => $data['num_casa'],
                ':estatus' => $data['estatus'],
                ':id_info' => $data['id_info']
            ]);

            // 3. Actualizar teléfono - CORREGIDO
            if (isset($data['id_telefono']) && isset($data['telefono'])) {
                $sqlPhone = "UPDATE priv_telusuario SET telefono = :telefono WHERE id_telefono = :id_telefono";
                $stmtPhone = $conn->prepare($sqlPhone);
                $stmtPhone->execute([':telefono' => $data['telefono'], ':id_telefono' => $data['id_telefono']]);
            }

            // 4. Actualizar correo
            if (isset($data['id_correo']) && isset($data['correo'])) {
                $sqlMail = "UPDATE priv_corresusuario SET correo = :correo WHERE id_correo = :id_correo";
                $stmtMail = $conn->prepare($sqlMail);
                $stmtMail->execute([':correo' => $data['correo'], ':id_correo' => $data['id_correo']]);
            }

            $conn->commit();
            return true;

        } catch (\PDOException $e) {
            $conn->rollBack();
            error_log("Error al actualizar residente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo residente, su cuenta de usuario y sus contactos.
     * @param array $data El payload del formulario de 'Añadir Residente'.
     * @return bool
     * @throws Exception
     */
    public function creaResidente(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // --- PASO 1: Crear la cuenta de usuario en `priv_usuarios` ---
            $sqlUser = "INSERT INTO priv_usuarios (usuario, contrasenia, id_estatus, id_rol, id_privada, num_casa)
                        VALUES (
                            :username, 
                            :password,
                            (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus), 
                            (SELECT id_rol FROM priv_roles WHERE rol = :rol),
                            (SELECT id_privada FROM priv_privadas WHERE nombre = :privada),
                            :num_casa
                        )";
            
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':estatus' => $data['estatus'],
                ':rol' => 'Usuario', 
                ':privada' => $data['privada'],
                ':num_casa' => $data['num_casa']
            ]);
            
            $id_usuario = $conn->lastInsertId();

            // --- PASO 2: Insertar la información personal en `priv_infousuario` ---
            $sqlInfo = "INSERT INTO priv_infousuario (id_usuario, nombres, apellido_p, apellido_m, fecha_nac, es_propietario)
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                $id_usuario,
                $data['nombres'],
                $data['apellido_p'],
                $data['apellido_m'],
                $data['fecha_nac'],
                $data['es_propietario']
            ]);
            
            $id_info = $conn->lastInsertId();

            // --- PASO 3: Insertar los correos ---
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, ?)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) {
                    $stmtMail->execute([$id_info, $correo,1]);
                }
            }

            // --- PASO 4: Insertar los teléfonos ---
            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, ?)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) {
                    $stmtPhone->execute([$id_info, $telefono,1]);
                }
            }

            $conn->commit();
            return true;
            
            $conn->rollBack();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Crea un nuevo residente "extra" (no propietario) y lo asocia
     * a un propietario existente basado en el número de casa.
     * @param array $data El payload del formulario 'Añadir Residente Extra'.
     * @return bool
     * @throws \Exception
     */
    public function creaResidenteExtra(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // --- PASO 1: Encontrar el id_usuario del PROPIETARIO de esa casa ---
            // Usamos el id_privada de la sesión y el num_casa del formulario
            $sqlFindUser = "SELECT u.id_usuario 
                            FROM priv_usuarios u 
                            JOIN priv_infousuario iu ON u.id_usuario = iu.id_usuario 
                            WHERE u.id_privada = :id_privada 
                              AND u.num_casa = :num_casa 
                              AND iu.es_propietario = 1 
                            LIMIT 1";
            
            $stmtFindUser = $conn->prepare($sqlFindUser);
            $stmtFindUser->execute([
                ':id_privada' => $data['id_privada'],
                ':num_casa' => $data['num_casa']
            ]);
            
            $id_usuario = $stmtFindUser->fetchColumn();

            if (!$id_usuario) {
                // Si no hay propietario, no podemos añadir un residente extra
                throw new \Exception('No se encontró un propietario para la casa ' . $data['num_casa'] . '. Verifique el número de casa.');
            }

            // --- PASO 2: Insertar la información personal en `priv_infousuario` ---
            // Se usa el id_usuario del propietario, pero se marcan como no-propietario (valor 0)
            $sqlInfo = "INSERT INTO priv_infousuario (id_usuario, nombres, apellido_p, apellido_m, fecha_nac, es_propietario)
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                $id_usuario,
                $data['nombres'],
                $data['apellido_p'],
                $data['apellido_m'],
                null, // No se pide fecha_nac en este formulario
                0     // Forzamos a 0 (No Propietario)
            ]);
            
            $id_info = $conn->lastInsertId();

            // --- PASO 3: Insertar los correos ---
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, 1)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) {
                    $stmtMail->execute([$id_info, $correo]);
                }
            }

            // --- PASO 4: Insertar los teléfonos ---
            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, 1)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) {
                    $stmtPhone->execute([$id_info, $telefono]);
                }
            }

            // --- PASO 5: Actualizar el estatus del usuario principal (priv_usuarios) ---
            // Esto es opcional, pero mantiene la consistencia
            $sqlEstatus = "UPDATE priv_usuarios SET id_estatus = (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus) WHERE id_usuario = :id_usuario";
            $stmtEstatus = $conn->prepare($sqlEstatus);
            $stmtEstatus->execute([
                ':estatus' => $data['estatus'],
                ':id_usuario' => $id_usuario
            ]);

            $conn->commit();
            return true;

        } catch (\Exception $e) {
            $conn->rollBack();
            // Re-lanzamos la excepción para que el controlador la atrape
            throw new \Exception("Error en la base de datos: " . $e->getMessage());
        }
        
    }
    /**
     * Elimina un residente y toda su información asociada.
     * OJO: El ID que recibimos es 'id_info', no 'id_usuario'.
     * @param int $id_info El ID de la tabla priv_infousuario.
     * @return bool
     * @throws Exception
     */
    public function eliminaResidente(int $id_info): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // Primero, necesitamos el 'id_usuario' para poder borrar la cuenta principal.
            $stmtUser = $conn->prepare("SELECT id_usuario FROM priv_infousuario WHERE id_info = ?");
            $stmtUser->execute([$id_info]);
            $id_usuario = $stmtUser->fetchColumn();

            if (!$id_usuario) {
                // Si no se encuentra el residente, no hay nada que borrar.
                $conn->commit();
                return true; 
            }

            // 1. Eliminar correos y teléfonos asociados a id_info.
            $conn->prepare("DELETE FROM priv_corresusuario WHERE id_info = ?")->execute([$id_info]);
            $conn->prepare("DELETE FROM priv_telusuario WHERE id_info = ?")->execute([$id_info]);

            // 2. Eliminar el registro de información personal.
            $conn->prepare("DELETE FROM priv_infousuario WHERE id_info = ?")->execute([$id_info]);

            // --- INICIO DE LA LÓGICA CORREGIDA ---
            
            // 3. Contar cuántos perfiles de info quedan para este id_usuario.
            $stmtCount = $conn->prepare("SELECT COUNT(*) FROM priv_infousuario WHERE id_usuario = ?");
            $stmtCount->execute([$id_usuario]);
            $remainingProfiles = $stmtCount->fetchColumn();

            // 4. Si no queda ningún perfil (count = 0),
            //    entonces es seguro eliminar la cuenta de usuario principal (priv_usuarios).
            if ($remainingProfiles == 0) {
                // Solo borramos el usuario principal si ya no hay nadie (info) que dependa de él.
                $conn->prepare("DELETE FROM priv_usuarios WHERE id_usuario = ?")->execute([$id_usuario]);
            }
            // Si $remainingProfiles > 0 (ej. el propietario sigue ahí),
            // no hacemos nada, dejando la cuenta principal intacta.
            
            // --- FIN DE LA LÓGICA CORREGIDA ---
            
            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            // Re-lanzar la excepción para que el controlador la maneje
            throw $e;
        }
       
    }
}