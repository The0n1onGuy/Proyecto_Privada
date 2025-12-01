<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;
use Exception;
class residentModeel {
    public function actualizaResident(array $data): array 
    {
        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            // 1. OBTENER DATOS ACTUALES
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
            $es_propietario_actual = $currentData['es_propietario']; // '1' o '0'
            $num_casa_actual = $currentData['num_casa'];
            $id_privada_actual = $currentData['id_privada'];
            
            $nuevo_rol_propietario = isset($data['es_propietario']) ? (string)$data['es_propietario'] : (string)$es_propietario_actual;
            $nuevo_estatus = $data['estatus'];
            $nuevo_num_casa = trim($data['num_casa']);

            // ---------------------------------------------------------
            // A. LÓGICA DE SUCESIÓN (Intercambio de Roles)
            // ---------------------------------------------------------
            
            // 1. Detección de Ascenso (Residente -> Propietario) en la misma casa
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

            // 2. Detección de Degradación/Desactivación (Propietario -> Residente/Inactivo)
            $es_degradacion = ($es_propietario_actual === '1' && $nuevo_rol_propietario === '0');
            $es_desactivacion = ($es_propietario_actual === '1' && $nuevo_estatus === 'Inactivo'); 

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

            // ---------------------------------------------------------
            // B. LÓGICA DE MUDANZA (Lo que faltaba)
            // ---------------------------------------------------------
            
            $id_usuario_destino = $id_usuario_actual; // Por defecto, se queda en su grupo actual

            // CASO 1: Es RESIDENTE y se cambia de casa
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

            // ---------------------------------------------------------
            // C. UPDATE PRINCIPAL (INFO USUARIO)
            // ---------------------------------------------------------
            
            // Preparar nombres
            $nombres = $data['nombres'] ?? ''; $apellido_p = $data['apellido_p'] ?? ''; $apellido_m = $data['apellido_m'] ?? '';
            if (!empty($data['nombreCompleto'])) {
               $parts = explode(' ', trim($data['nombreCompleto']));
               $count = count($parts);
               if ($count === 1) { $nombres = $parts[0]; }
               elseif ($count === 2) { $nombres = $parts[0]; $apellido_p = $parts[1]; }
               else { $apellido_m = array_pop($parts); $apellido_p = array_pop($parts); $nombres = implode(' ', $parts); }
            }

            // Aquí aplicamos el cambio de casa si $id_usuario_destino cambió (Caso Residente)
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

            // Actualizar contactos
            if (!empty($data['id_telefono']) && !empty($data['telefono'])) {
                 $conn->prepare("UPDATE priv_telusuario SET telefono = ? WHERE id_telefono = ? AND id_info = ?")->execute([$data['telefono'], $data['id_telefono'], $id_info_interno]);
            }
            if (!empty($data['id_correo']) && !empty($data['correo'])) {
                 $conn->prepare("UPDATE priv_corresusuario SET correo = ? WHERE id_correo = ? AND id_info = ?")->execute([$data['correo'], $data['id_correo'], $id_info_interno]);
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Residente actualizado correctamente.'];

        } catch (\Exception $e) {
            $conn->rollBack();
            error_log("Error update resident: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }
}