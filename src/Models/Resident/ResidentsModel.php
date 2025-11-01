<?php

namespace App\Models\Resident;

use App\Core\Database;
use PDO;

class ResidentsModel {

    /**
     * Obtiene los residentes de una privada específica, con un filtro opcional.
     *
     * @param int $id_privada El ID de la privada.
     * @param string $filterType El tipo de filtro ('owners' para solo propietarios, 'all' para todos).
     * @return array
     */
    public function getAllResidents($id_privada, $filterType = 'owners') {
        try {
            $conn = Database::getConnection();
            $sql = "
                SELECT
                    iu.id_info,
                    iu.id_usuario,
                    iu.nombres,
                    iu.apellido_p,
                    iu.apellido_m,
                    u.num_casa,
                    p.nombre AS privada_nombre,
                    e.estatus,
                    GROUP_CONCAT(DISTINCT ct.correo SEPARATOR ', ') AS correos,
                    GROUP_CONCAT(DISTINCT tt.telefono SEPARATOR ', ') AS telefonos,
                    iu.es_propietario
                FROM priv_infousuario iu
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario 
                JOIN priv_estatus e ON u.id_estatus = e.id_estatus
                JOIN priv_privadas p ON u.id_privada = p.id_privada
                LEFT JOIN priv_corresusuario ct ON iu.id_info = ct.id_info
                LEFT JOIN priv_telusuario tt ON iu.id_info = tt.id_info
                WHERE u.id_privada = :id_privada";

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
            $stmt->bindparam(':id_privada', $id_privada, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // Manejo de errores
            error_log("Error al obtener los residentes: " . $e->getMessage());
            return [];
        }
    }
}