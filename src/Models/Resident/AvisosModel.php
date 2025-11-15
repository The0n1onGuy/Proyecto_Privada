<?php

namespace App\Models\Resident;

use App\Core\Database;
use PDO;

class AvisosModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Obtiene todos los avisos de una privada específica.
     * @param int $id_privada
     * @return array
     */
public function getAllAvisos($id_privada)
    {
        try {
            $sql = "
                SELECT 
                    a.id_aviso,
                    a.tipo,
                    a.titulo,
                    a.contenido,
                    a.fecha_pub,
                    iu.nombres,
                    iu.apellido_p,
                    u.num_casa,
                    u.id_usuario  /* <-- AÑADE ESTA LÍNEA */
                FROM priv_avisos a
                JOIN priv_infousuario iu ON a.id_info = iu.id_info
                JOIN priv_usuarios u ON iu.id_usuario = u.id_usuario
                WHERE u.id_privada = :id_privada
                ORDER BY a.fecha_pub DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_privada', $id_privada, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // En un entorno de producción, registraríamos el error.
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo aviso en la base de datos.
     * @param array $data
     * @return bool
     */
public function createAviso($data)
{
    try {
        // El estatus lo definimos directamente como 1 (Activo).
        $sql = "INSERT INTO priv_avisos (tipo, titulo, contenido, fecha_pub, id_info, estatus)
                VALUES (:tipo, :titulo, :contenido, NOW(), :id_info, 1)";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':tipo' => $data['tipo'],
            ':titulo' => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':id_info' => $data['id_info']
        ]);

    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
/**
 * Elimina un aviso de la base de datos.
 * Solo elimina si el id_aviso Y el id_info (propietario) coinciden.
 *
 * @param int $id_aviso El ID del aviso a eliminar.
 * @param int $id_info El ID de info del usuario (de la sesión) que intenta eliminar.
 * @return bool True si se eliminó, False en caso contrario.
 */
public function deleteAviso(int $id_aviso, int $id_info): bool
{
    try {
        // La consulta DELETE comprueba tanto el ID del aviso como el ID del propietario
        $sql = "DELETE FROM priv_avisos 
                WHERE id_aviso = :id_aviso AND id_info = :id_info";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id_aviso', $id_aviso, PDO::PARAM_INT);
        $stmt->bindParam(':id_info', $id_info, PDO::PARAM_INT);
        
        $stmt->execute();

        // Devuelve true solo si se afectó al menos una fila (es decir, si se encontró y borró)
        return $stmt->rowCount() > 0;

    } catch (\PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
}