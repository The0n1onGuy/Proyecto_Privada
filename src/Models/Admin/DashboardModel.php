<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

/**
 * Modelo para gestionar los datos específicos del Dashboard del Administrador.
 */
class DashboardModel {
    
    /**
     * Obtiene las estadísticas de pagos agrupadas por método de pago.
     * Estos datos se usarán para alimentar la gráfica.
     *
     * @return array
     */
    public function getPaymentStats() {
        try {
            $conn = Database::getConnection();
            
            // Consulta SQL para sumar las cantidades de la tabla priv_pagos
            // y agruparlas por el nombre del método de pago de la tabla priv_metpagos.
            $sql = "
                SELECT 
                    mp.metstag AS metodo_pago, 
                    SUM(p.cantidad) AS total 
                FROM priv_pagos p
                JOIN priv_metpagos mp ON p.id_metstag = mp.id_metstag
                GROUP BY mp.metstag
                ORDER BY total DESC
            ";
            
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se manejaría el error (ej. log)
            // Por ahora, devolvemos un array vacío para evitar que la aplicación se rompa.
            return [];
        }
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
            
        $sql = "SELECT * FROM priv_avisos ";

            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // En un caso real, aquí se registraría el error.
            return [];
        }
    }
}
