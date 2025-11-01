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
    // Obtener todas las visitas
    public function getAllVisitas($userId) {
        $stmt = $this->conn->prepare(
            'SELECT * FROM priv_visitas WHERE id_usuario = :id_usuario'
        );
        $stmt->execute(['id_usuario' => $userId]);
        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    // Registrar una nueva visita
    public function createVisita($data) {
        try {
            $conn = Database::getConnection();
            $sql = "INSERT INTO priv_visitas (
                        nombre_visitante, apellido_visitante, tipo_visita, identificacion,
                        fecha_ingreso, id_residente, estatus, observaciones
                    ) VALUES (
                        :nombre_visitante, :apellido_visitante, :tipo_visita, :identificacion,
                        :fecha_ingreso, :id_residente, :estatus, :observaciones
                    )";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre_visitante', $data['nombre_visitante']);
            $stmt->bindParam(':apellido_visitante', $data['apellido_visitante']);
            $stmt->bindParam(':tipo_visita', $data['tipo_visita']);
            $stmt->bindParam(':identificacion', $data['identificacion']);
            $stmt->bindParam(':fecha_ingreso', $data['fecha_ingreso']);
            $stmt->bindParam(':id_residente', $data['id_residente']);
            $stmt->bindParam(':estatus', $data['estatus']);
            $stmt->bindParam(':observaciones', $data['observaciones']);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Actualizar estado de visita (finalizar, cancelar, etc.)
    public function updateEstatusVisita($id_visita, $nuevo_estatus, $fecha_salida = null) {
        try {
            $conn = Database::getConnection();
            $sql = "UPDATE priv_visitas SET estatus = :estatus";
            if ($fecha_salida) {
                $sql .= ", fecha_salida = :fecha_salida";
            }
            $sql .= " WHERE id_visita = :id_visita";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':estatus', $nuevo_estatus);
            $stmt->bindParam(':id_visita', $id_visita);
            if ($fecha_salida) {
                $stmt->bindParam(':fecha_salida', $fecha_salida);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
}