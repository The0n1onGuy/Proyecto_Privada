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
    public function getMonthlyActivitySummary($fecha_inicio, $fecha_fin, $collaboratorPublicId) {
        
        $sql = "
            SELECT
                P.fecha_programada AS activity_date,
                E.estatus AS status_name,
                COUNT(P.programada_id) AS activity_count
            FROM
                priv_actividades_programadas AS P
            JOIN
                priv_estatus AS E ON P.id_estatus = E.id_estatus
            JOIN 
                priv_usuarios AS U ON P.usuario_id_responsable = U.id_usuario
            WHERE P.fecha_programada BETWEEN ? AND ? 
            AND U.public_id = ?
            GROUP BY P.fecha_programada, E.estatus
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin, $collaboratorPublicId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getMonthlyActivitySummary: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles (Lista de actividades) para el modal.
     * Filtra por fecha y por las privadas asignadas al colaborador.
     * 
     * @param string $fecha La fecha en formato YYYY-MM-DD
     * @param string $collaboratorPublicId El public_id del colaborador
     * @return array Lista de actividades para esa fecha en las privadas del colaborador
     */
    public function getActivitiesForDate($fecha, $collaboratorPublicId) {
        $sql = "
            SELECT
                P.programada_id,
                P.fecha_programada,
                T.nombre AS nombre_actividad, 
                S.nom_serv AS nombre_servicio,
                COALESCE(U.usuario, 'Sin Asignar') AS nombre_responsable,
                E.estatus AS nombre_estatus,
                PR.nombre AS nombre_privada
            FROM
                priv_actividades_programadas AS P
            JOIN priv_privadas AS PR ON P.id_privada_fk = PR.id_privada
            JOIN priv_actividades_tipos AS T ON P.actividad_tipo_id = T.actividad_tipo_id
            LEFT JOIN priv_servicios AS S ON T.id_servicio_fk = S.id_servicio
            JOIN priv_estatus AS E ON P.id_estatus = E.id_estatus
            LEFT JOIN priv_usuarios AS U ON P.usuario_id_responsable = U.id_usuario
            WHERE
                P.fecha_programada = ?
                AND P.id_privada_fk IN (
                    SELECT id_privada FROM priv_usuarios 
                    WHERE public_id = ?
                )
            ORDER BY T.nombre ASC
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$fecha, $collaboratorPublicId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log para debuggeo
            error_log("getActivitiesForDate - Fecha: $fecha, PublicId: $collaboratorPublicId, Resultados: " . count($result));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en getActivitiesForDate: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un reporte para una actividad programada.
     * 
     * @param int $programada_id ID de la actividad programada
     * @param int $usuario_id_reporta ID del usuario que reporta
     * @param string $descripcion_ejecucion Descripción de cómo se ejecutó
     * @param int $hubo_incidencia Si hubo incidencia (0 o 1)
     * @param string|null $descripcion_incidencia Descripción de la incidencia
     * @return array Array con 'success' => true/false y 'message'
     */
    public function createActivityReport($programada_id, $usuario_id_reporta, $descripcion_ejecucion, $hubo_incidencia, $descripcion_incidencia = null) {
        $sql = "
            INSERT INTO priv_actividades_reportes 
            (programada_id, usuario_id_reporta, descripcion_ejecucion, hubo_incidencia, descripcion_incidencia)
            VALUES (?, ?, ?, ?, ?)
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $programada_id,
                $usuario_id_reporta,
                $descripcion_ejecucion,
                $hubo_incidencia,
                $descripcion_incidencia
            ]);

            error_log("Reporte creado exitosamente para actividad $programada_id");
            return [
                'success' => true,
                'message' => 'Reporte guardado correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error al crear reporte: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al guardar el reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene todos los reportes para las actividades de una privada.
     * 
     * @param int $id_privada ID de la privada
     * @return array Lista de reportes
     */
    public function getReportsByPrivada($id_privada) {
        $sql = "
            SELECT
                R.reporte_id,
                R.public_id,
                R.fecha_reporte,
                R.descripcion_ejecucion,
                R.hubo_incidencia,
                R.descripcion_incidencia,
                P.programada_id,
                P.fecha_programada,
                T.nombre AS nombre_actividad,
                U.usuario AS nombre_usuario_reporta,
                UR.usuario AS nombre_responsable,
                PR.nombre AS nombre_privada,
                E.estatus AS nombre_estatus
            FROM
                priv_actividades_reportes AS R
            JOIN priv_actividades_programadas AS P ON R.programada_id = P.programada_id
            JOIN priv_actividades_tipos AS T ON P.actividad_tipo_id = T.actividad_tipo_id
            JOIN priv_usuarios AS U ON R.usuario_id_reporta = U.id_usuario
            LEFT JOIN priv_usuarios AS UR ON P.usuario_id_responsable = UR.id_usuario
            JOIN priv_privadas AS PR ON P.id_privada_fk = PR.id_privada
            JOIN priv_estatus AS E ON P.id_estatus = E.id_estatus
            WHERE
                P.id_privada_fk = ?
            ORDER BY R.fecha_reporte DESC
        ";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_privada]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getReportsByPrivada: " . $e->getMessage());
            return [];
        }
    }
}