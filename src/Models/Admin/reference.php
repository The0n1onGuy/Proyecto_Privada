<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use Exception;

class reference {

    /**
     * Obtiene los residentes. AHORA OBTIENE EL PUBLIC_ID.
     */
    public function getAllResidents(string $public_id_privada, $filterType = 'owners') {
        try {
            $conn = Database::getConnection();
            $sql = "
                SELECT
                    iu.id_info,
                    iu.public_id, -- <--- IMPORTANTE: Agregamos esto
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
    
    // ... (getResidentById se puede quedar igual o adaptarlo si lo usas con UUID en la API) ...
    public function getResidentById($id_info) {
        // Nota: Si tu API recibe UUID, aquí deberías traducir. 
        // Por ahora asumo que tu API interna usa ID, dejémoslo así para no romper el modal de edición.
        try {
            $conn = Database::getConnection();
            
            $sql_main = "
                SELECT 
                    iu.id_info, iu.public_id, iu.nombres, iu.apellido_p, iu.apellido_m,
                    u.num_casa, p.nombre AS privada_nombre, e.estatus, iu.es_propietario
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                JOIN priv_privadas p ON u.id_privada = p.id_privada
                WHERE iu.id_info = :id_info"; // Si el JS envía id_info numérico (del data-id antiguo), esto funciona.
            
            $stmt_main = $conn->prepare($sql_main);
            $stmt_main->execute([':id_info' => $id_info]);
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
     * Actualiza residente usando UUID (Patrón de Traducción).
     */
    public function actualizaResident(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // --- PASO 1: TRADUCCIÓN DE UUID A ID INTERNO ---
            // Buscamos el id_info y id_usuario usando el public_id (que viene del form como 'id_residente' o similar)
            // Asegúrate de que tu JS envíe el UUID en el campo correcto.
            
            $public_id = $data['public_id_info']; // <--- CAMBIO: Esperamos UUID aquí

            $stmtId = $conn->prepare("SELECT id_info, id_usuario FROM priv_infousuario WHERE public_id = ?");
            $stmtId->execute([$public_id]);
            $ids = $stmtId->fetch(PDO::FETCH_ASSOC);

            if (!$ids) {
                throw new Exception("Residente no encontrado.");
            }
            
            $id_info_interno = $ids['id_info'];
            $id_usuario_interno = $ids['id_usuario'];

            // --- PASO 2: ACTUALIZAR USANDO IDs INTERNOS ---

            // 1. Actualizar priv_infousuario
            $nameParts = explode(' ', $data['nombreCompleto'], 3); // O usa nombres/apellidos separados si ya vienen así
            // Nota: Si tu form envía nombres separados, úsalos. Si envía 'nombreCompleto', usa el explode.
            // Asumiré que envías separados para ser consistente con 'creaResidente'.
            
            $sqlInfo = "UPDATE priv_infousuario SET nombres = :nombres, apellido_p = :apellido_p, apellido_m = :apellido_m, es_propietario = :es_propietario WHERE id_info = :id_info";
            $stmtInfo = $conn->prepare($sqlInfo);
            $stmtInfo->execute([
                ':nombres' => $data['nombres'], // Asumiendo inputs separados
                ':apellido_p' => $data['apellido_p'],
                ':apellido_m' => $data['apellido_m'],
                ':es_propietario' => $data['es_propietario'],
                ':id_info' => $id_info_interno
            ]);

            // 2. Actualizar priv_usuarios
            $sqlUser = "UPDATE priv_usuarios SET num_casa = :num_casa, id_estatus = (SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus) WHERE id_usuario = :id_usuario";
            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->execute([
                ':num_casa' => $data['num_casa'],
                ':estatus' => $data['estatus'],
                ':id_usuario' => $id_usuario_interno
            ]);

            // 3. Actualizar teléfono (Usamos IDs internos de teléfono, el frontend los envía)
            if (!empty($data['id_telefono']) && !empty($data['telefono'])) {
                $sqlPhone = "UPDATE priv_telusuario SET telefono = :telefono WHERE id_telefono = :id_telefono AND id_info = :id_info";
                $stmtPhone = $conn->prepare($sqlPhone);
                $stmtPhone->execute([
                    ':telefono' => $data['telefono'], 
                    ':id_telefono' => $data['id_telefono'],
                    ':id_info' => $id_info_interno // Seguridad extra
                ]);
            }

            // 4. Actualizar correo
            if (!empty($data['id_correo']) && !empty($data['correo'])) {
                $sqlMail = "UPDATE priv_corresusuario SET correo = :correo WHERE id_correo = :id_correo AND id_info = :id_info";
                $stmtMail = $conn->prepare($sqlMail);
                $stmtMail->execute([
                    ':correo' => $data['correo'], 
                    ':id_correo' => $data['id_correo'],
                    ':id_info' => $id_info_interno
                ]);
            }

            $conn->commit();
            return true;

        } catch (\Exception $e) {
            $conn->rollBack();
            error_log("Error al actualizar residente: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea un nuevo residente CON UUIDs.
     */
    public function creaResidente(array $data): bool
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // --- PASO 1: Crear usuario en `priv_usuarios` CON UUID ---
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
     * Crea residente extra CON UUIDs.
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
     * Elimina residente usando UUID (Patrón de Traducción).
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