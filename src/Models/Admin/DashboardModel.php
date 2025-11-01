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
    public function obtenPagosStat($id_privada) {
        try {
        $conn = Database::getConnection();
        $sql = "
                SELECT 
                    pe.estatus,  -- El nombre del estatus (ej. 'Completado')
                    COUNT(pp.id_pago) AS total_count
                FROM 
                    priv_pagos AS pp
                
                -- Solicitud 1: Unir con usuarios para obtener la privada
                JOIN 
                    priv_usuarios AS pu ON pp.id_usuario = pu.id_usuario
                
                -- Unir con estatus para obtener el nombre
                JOIN 
                    priv_estatus AS pe ON pp.id_estatuspago = pe.id_estatus
                
                WHERE 
                    -- Solicitud 1: Filtrar por el id_privada del *usuario*
                    pu.id_privada = :id_privada
                    
                    -- Solicitud 2: Filtrar por los IDs de estatus usando las constantes
                    AND pp.id_estatuspago IN (:id_c, :id_p, :id_m)
                
                GROUP BY 
                    pe.estatus
            ";

            // Usamos una consulta preparada para seguridad
            $stmt = $conn->prepare($sql);
            
            // Vincular el ID de la privada
            $stmt->bindParam(':id_privada', $id_privada, PDO::PARAM_INT);
            
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
            // En un caso real, aquí se manejaría el error (ej. log)
            // Devolvemos la estructura base con ceros (no placeholders)
            return [
                'completado' => 0,
                'pendiente' => 0,
                'moroso' => 0
            ];
        }
        // try {
        //     $conn = Database::getConnection();
            
        //     // Consulta SQL para sumar las cantidades de la tabla priv_pagos
        //     // y agruparlas por el nombre del método de pago de la tabla priv_metpagos.
        //     $sql = "
        //         SELECT 
        //             mp.metstag AS metodo_pago, 
        //             SUM(p.cantidad) AS total 
        //         FROM priv_pagos p
        //         JOIN priv_metpagos mp ON p.id_metstag = mp.id_metstag
        //         GROUP BY mp.metstag
        //         ORDER BY total DESC
        //     ";
            
        //     $stmt = $conn->query($sql);
        //     return $stmt->fetchAll(PDO::FETCH_ASSOC);

        // } catch (\PDOException $e) {
        //     // En un caso real, aquí se manejaría el error (ej. log)
        //     // Por ahora, devolvemos un array vacío para evitar que la aplicación se rompa.
        //     return [];
        // }
    }
    //Queries de consultas para reportes y avisos 280925 OLAN 
    //Consulta los reportes
    public function getReportes() {
        try {
            $conn = Database::getConnection();
            
            $sql = "SELECT * FROM priv_reportes";
            
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se registraría el error.
            return [];
        }
    }
    public function getAvisosD() {
        try {
            $conn = Database::getConnection();
            
        $sql = "SELECT * FROM priv_avisos";

            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se registraría el error.
            return [];
        }
    }
}
