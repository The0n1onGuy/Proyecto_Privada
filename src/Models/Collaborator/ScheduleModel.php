<?php

namespace App\Models\Collaborator;

use App\Core\Database;
use PDO;
use PDOException;

class ScheduleModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Obtiene un resumen de actividades (conteo por estatus) para un rango de fechas.
     *
     * @param string $fecha_inicio 'YYYY-MM-DD'
     * @param string $fecha_fin 'YYYY-MM-DD'
     * @param int|null $id_usuario_responsable (Opcional) para el filtro "Mis Actividades"
     * @return array
     */
public function getMonthlyActivitySummary($fecha_inicio, $fecha_fin, $id_usuario_responsable = null) {
    
    $sql = "
        SELECT
            P.fecha_programada,
            E.estatus AS nombre_estatus, -- CAMBIO CLAVE: Usa 'E.estatus'
            -- (Si no tienes columna de color, puedes añadir un CASE aquí)
            COUNT(P.programada_id) AS total_actividades
        FROM
            priv_actividades_programadas AS P
        JOIN
            priv_estatus AS E ON P.id_estatus = E.id_estatus
        WHERE
            P.fecha_programada BETWEEN ? AND ?
    ";
    
    $params = [$fecha_inicio, $fecha_fin];

    if ($id_usuario_responsable !== null) {
        $sql .= " AND P.usuario_id_responsable = ?";
        $params[] = $id_usuario_responsable;
    }
    
    $sql .= "
        GROUP BY
            P.fecha_programada, E.estatus -- CAMBIO CLAVE: Usa 'E.estatus'
        ORDER BY
            P.fecha_programada;
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el detalle completo de todas las actividades para una fecha específica.
     *
     * @param string $fecha 'YYYY-MM-DD'
     * @param int|null $id_usuario_responsable (Opcional) para el filtro "Mis Actividades"
     * @return array
     */
    public function getDetailedActivitiesForDate($fecha, $id_usuario_responsable = null) {
        
        $sql = "
            SELECT
                P.public_id AS actividad_public_id,
                R.public_id AS reporte_public_id,
                P.fecha_programada,
                PR.nombre AS nombre_privada,
                S.nom_serv AS nombre_servicio,
                T.nombre AS nombre_actividad,
                U.usuario AS nombre_responsable,
                E.estatus AS nombre_estatus, -- CAMBIO CLAVE: Usa 'E.estatus'
                (CASE WHEN R.reporte_id IS NOT NULL THEN 'Sí' ELSE 'No' END) AS tiene_reporte
            FROM
                priv_actividades_programadas AS P
            JOIN priv_privadas AS PR ON P.id_privada_fk = PR.id_privada
            JOIN priv_actividades_tipos AS T ON P.actividad_tipo_id = T.actividad_tipo_id
            JOIN priv_servicios AS S ON T.id_servicio_fk = S.id_servicio
            JOIN priv_estatus AS E ON P.id_estatus = E.id_estatus -- Esta unión es correcta
            LEFT JOIN priv_usuarios AS U ON P.usuario_id_responsable = U.id_usuario
            LEFT JOIN priv_actividades_reportes AS R ON P.programada_id = R.programada_id
            WHERE
                P.fecha_programada = ?
        ";

        $params = [$fecha];

        // Añadir el filtro "Mis Actividades" si se proporciona
        if ($id_usuario_responsable !== null) {
            $sql .= " AND P.usuario_id_responsable = ?";
            $params[] = $id_usuario_responsable;
        }

        $sql .= " ORDER BY PR.nombre, T.nombre;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}