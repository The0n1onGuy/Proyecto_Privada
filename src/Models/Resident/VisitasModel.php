<?php

namespace App\Models\Resident;

use App\Core\Database;
use PDO;

class VisitasModel
{
    private $conn;
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }
    /**
     * OBTENER VISITAS (CON MIGRACIÓN PUBLIC_ID)
     * Esta función está CORRECTA como la dejamos.
     */
    public function getAllVisitas($userPublicId) {
        $stmt = $this->conn->prepare(
            'SELECT * FROM priv_visitas 
             WHERE id_usuario = (SELECT id_usuario FROM priv_usuarios WHERE public_id = :user_public_id)'
        );
        $stmt->execute(['user_public_id' => $userPublicId]);
        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    /**
     * CREAR VISITA (CON BUG ARREGLADO + MIGRACIÓN PUBLIC_ID)
     */
    public function createVisita($data) {
        try {
            $conn = Database::getConnection();
            
            // Esta consulta ahora SÍ coincide con el formulario que arreglamos
            // y maneja la migración de public_id.
            $sql = "INSERT INTO priv_visitas (
                        nombre_visitante, 
                        apellido_visitante, 
                        tipo_visita, 
                        id_usuario, 
                        observaciones, 
                        estatus
                    ) VALUES (
                        :nombre_visitante, 
                        :apellido_visitante, 
                        :tipo_visita, 
                        (SELECT id_usuario FROM priv_usuarios WHERE public_id = :user_public_id), 
                        :observaciones, 
                        :estatus
                    )";
            
            $stmt = $conn->prepare($sql);
            
            // Bindeamos los datos que SÍ vienen del formulario
            $stmt->bindParam(':nombre_visitante', $data['nombre_visitante']);
            $stmt->bindParam(':apellido_visitante', $data['apellido_visitante']);
            $stmt->bindParam(':tipo_visita', $data['tipo_visita']);
            
            // Usamos el public_id que viene del formulario para encontrar el id_usuario real
            $stmt->bindParam(':user_public_id', $data['id_residente']); 
            
            $stmt->bindParam(':observaciones', $data['observaciones']);
            $stmt->bindParam(':estatus', $data['estatus']);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("SQL Error en VisitasModel: " . $e->getMessage()); 
            return false;
        }
    }

    /**
     * ACTUALIZAR ESTATUS (CON BUG ARREGLADO)
     * Dejamos esta función como la original, usando el ID NUMÉRICO.
     * El error de red ocurría porque intentamos migrarla a public_id
     * cuando el JS y la vista envían 'id_visita'.
     */
    public function updateEstatusVisita($id_visita, $nuevo_estatus, $fecha_salida = null) {
        try {
            $conn = Database::getConnection();
            $sql = "UPDATE priv_visitas SET estatus = :estatus";
            if ($fecha_salida) {
                $sql .= ", fecha_salida = :fecha_salida";
            }
            $sql .= " WHERE id_visita = :id_visita"; // <-- Mantenemos el ID numérico
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':estatus', $nuevo_estatus);
            $stmt->bindParam(':id_visita', $id_visita); // <-- Mantenemos el ID numérico
            if ($fecha_salida) {
                $stmt->bindParam(':fecha_salida', $fecha_salida);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            // ¡Esto escribirá el error SQL exacto en tu archivo de logs!
            error_log("SQL Error en VisitasModel: " . $e->getMessage()); 
            return false;
        }
    }
}