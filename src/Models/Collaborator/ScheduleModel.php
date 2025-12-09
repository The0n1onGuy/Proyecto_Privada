<?php

namespace App\Models\Collaborator;

use App\Core\Database;
use PDO;
use PDOException;
use Exception;

class ScheduleModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }
        /**
     * Obtiene todos los horarios para un colaborador específico en un mes y año dados.
     * * @param int $year El año a consultar.
     * @param int $month El mes a consultar (1-12).
     * @param int $collaboratorId El ID del colaborador.
     * @return array La lista de horarios programados.
     */
    public function getMonthlySchedule(int $year, int $month, int $collaboratorId = 1): array {
        try {
            // Asegurarse de que el mes y el año sean válidos
            if ($month < 1 || $month > 12 || $year < 2000) {
                return [];
            }

            // Formato del mes para la consulta (ej. '2025-05')
            $monthStr = sprintf('%d-%02d', $year, $month);

            // Consulta SQL moderna utilizando parámetros con nombre para evitar inyección SQL.
            // La cláusula WHERE utiliza LIKE para encontrar todas las fechas que comienzan con el año y el mes.
            // Asume que el campo de la fecha/hora se llama 'start_time' y el ID del colaborador 'collaborator_id'.
            $sql = "
                SELECT 
                    id, 
                    title, 
                    start_time, 
                    end_time 
                FROM schedules 
                WHERE collaborator_id = :collaborator_id 
                AND start_time LIKE :month_start 
                ORDER BY start_time ASC
            ";

            $stmt = $this->conn->prepare($sql);
            
            // Vincular parámetros de forma segura
            $stmt->bindParam(':collaborator_id', $collaboratorId, PDO::PARAM_INT);
            $stmt->bindValue(':month_start', $monthStr . '-%', PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Manejo de errores de la base de datos. 
            // En un entorno de producción, DEBERÍAS registrar el error y no mostrarlo directamente.
            error_log("Error al obtener horario mensual: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            error_log("Error general en ScheduleModel: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un resumen numérico para pintar el calendario (los cuadritos de colores).
     */
    public function getMonthlyActivitySummary($fecha_inicio, $fecha_fin, $userPublicId = null) {
        
        $sql = "
            SELECT
                P.fecha_programada,
                E.estatus AS nombre_estatus,
                COUNT(P.programada_id) AS total_actividades
            FROM
                priv_actividades_programadas AS P
            JOIN
                priv_estatus AS E ON P.id_estatus = E.id_estatus
        ";

        // Joins necesarios para filtros
        if ($userPublicId !== null) {
            $sql .= " LEFT JOIN priv_usuarios AS U ON P.usuario_id_responsable = U.id_usuario ";
        }

        $sql .= " WHERE P.fecha_programada BETWEEN ? AND ? ";
        $params = [$fecha_inicio, $fecha_fin];

        if ($userPublicId !== null) {
            $sql .= " AND U.public_id = ? ";
            $params[] = $userPublicId;
        }

        $sql .= " GROUP BY P.fecha_programada, E.estatus";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getMonthlyActivitySummary: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles (Lista de actividades) para el modal.
     * AJUSTADO A TU BASE DE DATOS: Sin hora_inicio, hora_fin, ni descripción manual.
     */
    public function getActivitiesForDate($fecha, $userPublicId = null) {
        $sql = "
            SELECT
                P.programada_id,
                P.fecha_programada,
                -- Usamos el nombre del tipo de actividad como descripción principal
                T.nombre AS nombre_actividad, 
                S.nom_serv AS nombre_servicio,
                -- Validamos si hay responsable asignado
                COALESCE(U.usuario, 'Sin Asignar') AS nombre_responsable,
                E.estatus AS nombre_estatus,
                PR.nombre_privada
            FROM
                priv_actividades_programadas AS P
            JOIN priv_privadas AS PR ON P.id_privada_fk = PR.id_privada
            JOIN priv_actividades_tipos AS T ON P.actividad_tipo_id = T.actividad_tipo_id
            LEFT JOIN priv_servicios AS S ON T.id_servicio_fk = S.id_servicio
            JOIN priv_estatus AS E ON P.id_estatus = E.id_estatus
            LEFT JOIN priv_usuarios AS U ON P.usuario_id_responsable = U.id_usuario
            WHERE
                P.fecha_programada = ?
        ";

        $params = [$fecha];

        if ($userPublicId !== null) {
            $sql .= " AND U.public_id = ?";
            $params[] = $userPublicId;
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getActivitiesForDate: " . $e->getMessage());
            return [];
        }
    }
}