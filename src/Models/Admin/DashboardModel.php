<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

/**
 * Modelo para gestionar los datos específicos del Dashboard del Administrador.
 */
class DashboardModel {
    private const ESTATUS_C= 3;
    private const ESTATUS_P = 4;
    private const ESTATUS_M = 5;
    
    /**
     * Obtiene las estadísticas de pagos agrupadas por método de pago.
     * Estos datos se usarán para alimentar la gráfica.
     *
     * @return array
     */
    public function obtenPagosStat(string $public_id_privada) {
        try {
        $conn = Database::getConnection();
        $sql = "
                SELECT 
                    pe.estatus,
                    COUNT(pp.id_pago) AS total_count
                FROM 
                    priv_pagos AS pp
                JOIN 
                    priv_usuarios AS pu ON pp.id_usuario = pu.id_usuario
                JOIN
                    priv_privadas AS pv ON pu.id_privada = pv.id_privada
                JOIN 
                    priv_estatus AS pe ON pp.id_estatuspago = pe.id_estatus
                WHERE 
                    pv.public_id = :public_id
                    AND pp.id_estatuspago IN (:id_c, :id_p, :id_m)
                
                GROUP BY 
                    pe.estatus
            ";

            // Usamos una consulta preparada para seguridad
            $stmt = $conn->prepare($sql);
            
            // Vincular el ID de la privada
            $stmt->bindParam(':public_id', $public_id_privada, PDO::PARAM_STR);
            
            // Vincular los IDs de estatus desde las constantes
            // Usamos bindValue porque estamos vinculando un valor, no una variable
            $stmt->bindValue(':id_c', self::ESTATUS_C, PDO::PARAM_INT);
            $stmt->bindValue(':id_p', self::ESTATUS_P, PDO::PARAM_INT);
            $stmt->bindValue(':id_m', self::ESTATUS_M, PDO::PARAM_INT);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Transforma el resultado de la BD en el objeto exacto que el JS espera.
            // Inicializa los contadores a 0.
            $stats = [
                'completado' => 0,
                'pendiente' => 0,
                'moroso' => 0
            ];

            // 3. Rellena el array $stats con los datos reales de la consulta
            // Este bloque sigue funcionando perfectamente
            foreach ($results as $row) {
                // Convertimos 'Completado' -> 'completado' para que sea la clave
                $status_key = strtolower($row['estatus']); 
                if (array_key_exists($status_key, $stats)) {
                    $stats[$status_key] = (int) $row['total_count'];
                }
            }

            // 4. Devuelve el objeto final
            return $stats;

        } catch (\PDOException $e) {
            //Damos el error y Devolvemos la estructura base con ceros (sin placeholders) 
            error_log("Error en obtenPagosStat: " . $e->getMessage());
            return [
                'completado' => 0,
                'pendiente' => 0,
                'moroso' => 0
            ];
        }
    }
    //Queries de consultas para reportes y avisos 280925 OLAN 
    //Consulta los reportes
    /**
     * Consulta los reportes de una privada específica.
     *
     * @param string $public_id_privada El UUID de la privada.
     * @return array
     */
    public function getReportes(string $public_id_privada) {
        try {
            $conn = Database::getConnection();
            // $sql = "
            //     SELECT r.* FROM priv_reportes r
            //     JOIN priv_privadas pv ON r.id_privada = pv.id_privada
            //     WHERE pv.public_id = ?
            // ";
            
            // $stmt = $conn->prepare($sql);
            // $stmt->execute([$public_id_privada]);
            // return $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT * FROM priv_reportes";
            
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error en getReportes: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Consulta los reportes de una privada específica.
     *
     * @param string $public_id_privada El UUID de la privada.
     * @return array
     */
    public function getAvisosD(string $public_id_privada) {
        try {
            $conn = Database::getConnection();
            // Realiza 3 consultas primero del id_info dentro de la tabla al id_usuario
            // dentro de la tabla de infousuario y finalmente utiliza el id_usuario para ver de que privada pertence
            $sql = "
                SELECT a.* FROM priv_avisos a
                JOIN priv_infousuario iu ON a.id_info = iu.id_info
                JOIN priv_usuarios pu ON iu.id_usuario = pu.id_usuario
                JOIN priv_privadas pv ON pu.id_privada = pv.id_privada
                WHERE pv.public_id = ?
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$public_id_privada]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            // $sql = "SELECT * FROM priv_avisos";
            // $stmt = $conn->query($sql);
            // return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se registraría el error.
            return [];
        }
    }
}
