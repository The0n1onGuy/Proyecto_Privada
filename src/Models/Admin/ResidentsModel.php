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
            $sql = "
                SELECT
                    iu.id_info,
                    iu.public_id, 
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

            if ($filterType === 'owners') {
                $sql .= " AND iu.es_propietario = 1";
            }

            $sql .= "
                GROUP BY iu.id_info
                ORDER BY u.num_casa ASC, iu.es_propietario DESC, iu.id_info ASC
                ";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':public_id' => $public_id_privada]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
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
    public function getResidentById($public_id) {
        // Nota: Si tu API recibe UUID, aquí deberías traducir. 
        // Por ahora asumo que tu API interna usa ID, dejémoslo así para no romper el modal de edición.
        try {
            $conn = Database::getConnection();
            
            $stmtInfo = $conn->prepare("SELECT id_info FROM priv_infousuario WHERE public_id = ?");
            $stmtInfo->execute([$public_id]);
            $id_info = $stmtInfo->fetchColumn();

            if (!$id_info) return false;

            // (Usando el id_info interno que acabamos de encontrar)
            // Tu consulta original ya funcionaba con id_info, así que la dejamos igual,
            // solo cambiamos el parámetro que recibe.
            
            $sql_main = "
                SELECT 
                    iu.id_info, iu.public_id, iu.nombres, iu.apellido_p, iu.apellido_m,
                    u.num_casa, p.nombre AS privada_nombre, e.estatus, iu.es_propietario
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                JOIN priv_privadas p ON u.id_privada = p.id_privada
                WHERE iu.id_info = :id_info"; 
            
            $stmt_main = $conn->prepare($sql_main);
            $stmt_main->execute([':id_info' => $id_info]); // Usamos el ID interno traducido
            $resident = $stmt_main->fetch(PDO::FETCH_ASSOC);

            if (!$resident) return false;

            // Obtener correos y teléfonos (usando id_info interno)
            $sql_emails = "SELECT id_correo, correo FROM priv_corresusuario WHERE id_info = :id_info AND id_estatus = 1";
            $stmt_emails = $conn->prepare($sql_emails);
            $stmt_emails->execute([':id_info' => $id_info]);
            $resident['correos'] = $stmt_emails->fetchAll(PDO::FETCH_ASSOC);

            $sql_phones = "SELECT id_telefono, telefono FROM priv_telusuario WHERE id_info = :id_info AND id_estatus = 1";
            $stmt_phones = $conn->prepare($sql_phones);
            $stmt_phones->execute([':id_info' => $id_info]);
            $resident['telefonos'] = $stmt_phones->fetchAll(PDO::FETCH_ASSOC);
            
            return $resident;

        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza residente con PROTOCOLO DE SUCESIÓN.
     * Retorna array con estado y datos adicionales si se requiere acción del usuario.
     */
    /**
     * Actualiza la información de un residente en la base de datos.
     *
     * @param array $data Datos del residente a actualizar.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizaResident(array $data): array // Cambiamos retorno a array
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // OBTENER DATOS ACTUALES
            $public_id = $data['public_id_info']; 
            
            $stmtCurrent = $conn->prepare("
                SELECT iu.id_info, iu.id_usuario, iu.es_propietario, u.num_casa, u.id_privada
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                WHERE iu.public_id = ?
            ");
            $stmtCurrent->execute([$public_id]);
            $currentData = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

            if (!$currentData) throw new Exception("Residente no encontrado.");
            
            $id_info_interno = $currentData['id_info'];
            $id_usuario_actual = $currentData['id_usuario'];
            $es_propietario_actual = $currentData['es_propietario'];
            $num_casa_actual = $currentData['num_casa'];
            $id_privada_actual = $currentData['id_privada'];
            
            // Datos entrantes
            $nuevo_rol_propietario = isset($data['es_propietario']) ? (string)$data['es_propietario'] : (string)$es_propietario_actual;
            $nuevo_estatus = $data['estatus'];
            $nuevo_num_casa = trim($data['num_casa']);

            if ($es_propietario_actual === '0' && $nuevo_rol_propietario === '1' && $nuevo_num_casa === $num_casa_actual) {
                $stmtOwnerCheck = $conn->prepare("SELECT id_info, nombres, apellido_p FROM priv_infousuario WHERE id_usuario = ? AND es_propietario = '1' AND id_info != ?");
                $stmtOwnerCheck->execute([$id_usuario_actual, $id_info_interno]);
                $existingOwner = $stmtOwnerCheck->fetch(PDO::FETCH_ASSOC);

                if ($existingOwner) {
                    if (empty($data['confirm_swap'])) {
                        $conn->rollBack();
                        return ['success' => false, 'requires_swap' => true, 'current_owner_name' => $existingOwner['nombres'] . ' ' . $existingOwner['apellido_p'], 'message' => 'Conflicto de propiedad detectado.'];
                    }
                    // Degradamos al anterior
                    $conn->prepare("UPDATE priv_infousuario SET es_propietario = '0' WHERE id_info = ?")->execute([$existingOwner['id_info']]);
                }
            }
            // ---------------------------------------------------------
            // DETECCIÓN DE "VACÍO DE PODER" (Propietario -> Residente/Inactivo)
            // ---------------------------------------------------------
            $es_degradacion = ($es_propietario_actual == 1 && $nuevo_rol_propietario == 0);
            $es_desactivacion = ($es_propietario_actual == 1 && $nuevo_estatus === 'Inactivo'); // Ajusta string según tu DB

            if ($es_degradacion || $es_desactivacion) {
                $stmtDependents = $conn->prepare("SELECT public_id, nombres, apellido_p FROM priv_infousuario WHERE id_usuario = ? AND es_propietario = '0' AND id_info != ?");
                $stmtDependents->execute([$id_usuario_actual, $id_info_interno]);
                $candidatos = $stmtDependents->fetchAll(PDO::FETCH_ASSOC);

                if (count($candidatos) > 0) {
                    if (empty($data['heir_public_id'])) {
                        $conn->rollBack();
                        return ['success' => false, 'requires_heir' => true, 'candidates' => $candidatos, 'message' => 'Se requiere asignar un nuevo propietario.'];
                    }
                    // Ascendemos al heredero
                    $conn->prepare("UPDATE priv_infousuario SET es_propietario = '1' WHERE public_id = ?")->execute([$data['heir_public_id']]);
                }
            }
            
            
            $id_usuario_destino = $id_usuario_actual; // Por defecto, se queda en su grupo actual
            if ($es_propietario_actual === '0' && $nuevo_num_casa !== $num_casa_actual) {
                
                // Buscar al dueño de la casa destino en la misma privada
                $stmtTargetOwner = $conn->prepare("
                    SELECT u.id_usuario 
                    FROM priv_usuarios u
                    JOIN priv_infousuario iu ON u.id_usuario = iu.id_usuario
                    WHERE u.num_casa = :num_casa 
                      AND u.id_privada = :id_privada
                      AND iu.es_propietario = '1'
                    LIMIT 1
                ");
                $stmtTargetOwner->execute([':num_casa' => $nuevo_num_casa, ':id_privada' => $id_privada_actual]);
                $newOwnerId = $stmtTargetOwner->fetchColumn();

                if (!$newOwnerId) {
                    throw new Exception("No se puede mover al residente: La casa '$nuevo_num_casa' no existe o no tiene un propietario asignado.");
                }

                // CAMBIO CLAVE: El residente cambia de ID padre (se muda)
                $id_usuario_destino = $newOwnerId; 
                
                // NOTA: NO actualizamos priv_usuarios, porque el residente solo se desvincula.
            } 
            
            // CASO 2: Es PROPIETARIO y cambia el número de su casa
            elseif ($es_propietario_actual === '1' && $nuevo_num_casa !== $num_casa_actual) {
                // Actualizamos la tabla padre, moviendo a toda la familia
                $conn->prepare("UPDATE priv_usuarios SET num_casa = :num_casa WHERE id_usuario = :id_usuario")
                     ->execute([':num_casa' => $nuevo_num_casa, ':id_usuario' => $id_usuario_actual]);
            }
            // Atomiza los nombres
            $nombres = $data['nombres'] ?? ''; 
            $apellido_p = $data['apellido_p'] ?? '';
            $apellido_m = $data['apellido_m'] ?? '';
            
            if (!empty($data['nombreCompleto'])) {
                $parts = explode(' ', trim($data['nombreCompleto']));
                $count = count($parts);
                if ($count === 1) { $nombres = $parts[0]; }
                elseif ($count === 2) { $nombres = $parts[0]; $apellido_p = $parts[1]; }
                elseif ($count === 3) { $nombres = $parts[0]; $apellido_p = $parts[1]; $apellido_m = $parts[2]; }
                else { $apellido_m = array_pop($parts); $apellido_p = array_pop($parts); $nombres = implode(' ', $parts); }
            } else {
                $nombres = $data['nombres'] ?? '';
                $apellido_p = $data['apellido_p'] ?? '';
                $apellido_m = $data['apellido_m'] ?? '';
            }

            // ---------------------------------------------------------
            // UPDATE PRINCIPAL
            // ---------------------------------------------------------
            
             $sqlInfo = "UPDATE priv_infousuario SET 
                            nombres = :nombres, 
                            apellido_p = :apellido_p, 
                            apellido_m = :apellido_m, 
                            es_propietario = :es_propietario,
                            id_usuario = :id_usuario_destino -- <--- ESTO MUEVE AL RESIDENTE
                        WHERE id_info = :id_info";
                        
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                ':nombres' => $nombres,
                ':apellido_p' => $apellido_p,
                ':apellido_m' => $apellido_m,
                ':es_propietario' => $nuevo_rol_propietario,
                ':id_usuario_destino' => $id_usuario_destino,
                ':id_info' => $id_info_interno
            ]);

            // Actualizar Estatus de la cuenta (Solo si no nos mudamos a otra casa ajena)
            if ($id_usuario_destino === $id_usuario_actual) {
                // Asumimos que si hay sucesión, la cuenta sigue activa
                $estatus_final = ($es_degradacion && !empty($data['heir_public_id'])) ? 'Activo' : $data['estatus'];
                
                $conn->prepare("UPDATE priv_usuarios SET id_estatus = (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus) WHERE id_usuario = :id_usuario")
                     ->execute([':estatus' => $estatus_final, ':id_usuario' => $id_usuario_actual]);
            }


            if (!empty($data['id_telefono']) && !empty($data['telefono'])) {
                 $sqlPhone = "UPDATE priv_telusuario SET telefono = ? WHERE id_telefono = ? AND id_info = ?";
                 $conn->prepare($sqlPhone)->execute([$data['telefono'], $data['id_telefono'], $id_info_interno]);
            }
            if (!empty($data['id_correo']) && !empty($data['correo'])) {
                 $sqlMail = "UPDATE priv_corresusuario SET correo = ? WHERE id_correo = ? AND id_info = ?";
                 $conn->prepare($sqlMail)->execute([$data['correo'], $data['id_correo'], $id_info_interno]);
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Residente actualizado correctamente.'];

        } catch (\Exception $e) {
            $conn->rollBack();
            error_log("Error update resident: " . $e->getMessage());
            // Retornamos array de error en lugar de lanzar excepción para controlar el flujo
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
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
            //  Crear usuario en `priv_usuarios` CON UUID ---
            $sqlUser = "INSERT INTO priv_usuarios (usuario, contrasenia, id_estatus, id_rol, id_privada, num_casa, public_id)
                        VALUES (
                            :username, 
                            :password,
                            (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus), 
                            (SELECT id_rol FROM priv_roles WHERE rol = :rol),
                            (SELECT id_privada FROM priv_privadas WHERE public_id = :public_id_privada), -- Usamos public_id_privada
                            :num_casa,
                            UUID() -- <--- GENERAMOS UUID
                        )";
            
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':estatus' => $data['estatus'],
                ':rol' => 'Usuario', 
                ':public_id_privada' => $data['public_id_privada'], // Cambiamos nombre a nombre del parámetro real
                ':num_casa' => $data['num_casa']
            ]);
            
            $id_usuario = $conn->lastInsertId();

            // --- PASO 2: Insertar info personal CON UUID ---
            // Nota: fecha_nac se maneja por default NULL en BD, la quitamos del insert si viene vacía o la dejamos si la envías.
            // Agregamos public_id UUID()
            $sqlInfo = "INSERT INTO priv_infousuario (id_usuario, nombres, apellido_p, apellido_m, fecha_nac, es_propietario, public_id)
                        VALUES (?, ?, ?, ?, ?, ?, UUID())";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                $id_usuario,
                $data['nombres'],
                $data['apellido_p'],
                $data['apellido_m'],
                !empty($data['fecha_nac']) ? $data['fecha_nac'] : null,
                $data['es_propietario']
            ]);
            
            $id_info = $conn->lastInsertId();

            // --- PASO 3 & 4: Insertar Contactos (Soft Delete Ready) ---
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, 1)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) {
                    $stmtMail->execute([$id_info, $correo]);
                }
            }

            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, 1)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) {
                    $stmtPhone->execute([$id_info, $telefono]);
                }
            }

            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollBack();
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
            // Buscar ID del propietario usando public_id_privada y num_casa
            $sqlFindUser = "SELECT u.id_usuario 
                            FROM priv_usuarios u 
                            JOIN priv_infousuario iu ON u.id_usuario = iu.id_usuario 
                            JOIN priv_privadas p ON u.id_privada = p.id_privada
                            WHERE p.public_id = :public_id_privada 
                              AND u.num_casa = :num_casa 
                              AND iu.es_propietario = 1 
                            LIMIT 1";
            
            $stmtFindUser = $conn->prepare($sqlFindUser);
            $stmtFindUser->execute([
                ':public_id_privada' => $data['public_id_privada'],
                ':num_casa' => $data['num_casa']
            ]);
            
            $id_usuario = $stmtFindUser->fetchColumn();

            if (!$id_usuario) {
                throw new \Exception('No se encontró un propietario para la casa ' . $data['num_casa']);
            }

            // Insertar info personal (extra) con UUID
            $sqlInfo = "INSERT INTO priv_infousuario (id_usuario, nombres, apellido_p, apellido_m, fecha_nac, es_propietario, public_id)
                        VALUES (?, ?, ?, ?, ?, ?, UUID())";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                $id_usuario,
                $data['nombres'],
                $data['apellido_p'],
                $data['apellido_m'],
                null, // fecha_nac default null
                0     // es_propietario = 0
            ]);
            
            $id_info = $conn->lastInsertId();

            // Insertar contactos
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresusuario (id_info, correo, id_estatus) VALUES (?, ?, 1)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) { $stmtMail->execute([$id_info, $correo]); }
            }
            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telusuario (id_info, telefono, id_estatus) VALUES (?, ?, 1)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) { $stmtPhone->execute([$id_info, $telefono]); }
            }

            $conn->commit();
            return true;

        } catch (\Exception $e) {
            $conn->rollBack();
            throw new \Exception("Error BD: " . $e->getMessage());
        }
        
    }
    /**
     * Elimina un residente y toda su información asociada.
     * OJO: El ID que recibimos es 'id_info', no 'id_usuario'.
     * @param int $id_info El ID de la tabla priv_infousuario.
     * @return bool
     * @throws Exception
     */
    public function eliminaResidente(string $public_id_info): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // --- PASO 1: Traducir UUID a IDs internos ---
            $stmt = $conn->prepare("SELECT id_info, id_usuario FROM priv_infousuario WHERE public_id = ?");
            $stmt->execute([$public_id_info]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                $conn->commit(); // No existe, nada que borrar
                return true; 
            }
            $id_info = $data['id_info'];
            $id_usuario = $data['id_usuario'];

            // --- PASO 2: Usar IDs internos para borrar ---
            
            // 1. Soft Delete o Hard Delete de contactos (Según tu preferencia, aquí usamos hard delete como estaba)
            $conn->prepare("DELETE FROM priv_corresusuario WHERE id_info = ?")->execute([$id_info]);
            $conn->prepare("DELETE FROM priv_telusuario WHERE id_info = ?")->execute([$id_info]);

            // 2. Eliminar info
            $conn->prepare("DELETE FROM priv_infousuario WHERE id_info = ?")->execute([$id_info]);

            // 3. Verificar si quedan perfiles para el usuario
            $stmtCount = $conn->prepare("SELECT COUNT(*) FROM priv_infousuario WHERE id_usuario = ?");
            $stmtCount->execute([$id_usuario]);
            $remaining = $stmtCount->fetchColumn();

            if ($remaining == 0) {
                $conn->prepare("DELETE FROM priv_usuarios WHERE id_usuario = ?")->execute([$id_usuario]);
            }
            
            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
       
    }
}