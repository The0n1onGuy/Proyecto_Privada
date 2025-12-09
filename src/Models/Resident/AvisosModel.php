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
     * @param string $privadaPublicId El public_id de la privada (desde la sesión).
     */
    public function getAllAvisos($privadaPublicId)
    {
        $sql = "SELECT 
                    a.id_aviso, 
                    a.titulo, 
                    a.contenido, 
                    a.tipo, 
                    a.fecha_pub, 
                    i.nombres, 
                    i.apellido_p, 
                    i.apellido_m,
                    u.num_casa,
                    u.public_id AS author_public_id  -- El UUID del autor
                FROM priv_avisos a
                JOIN priv_infousuario i ON a.id_info = i.id_info
                JOIN priv_usuarios u ON i.id_usuario = u.id_usuario
                WHERE u.id_privada = (SELECT id_privada FROM priv_privadas WHERE public_id = :privada_public_id)
                ORDER BY a.fecha_pub DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['privada_public_id' => $privadaPublicId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAviso($data)
    {
        try {
            // CORRECCIÓN 2: Insertamos en 'id_info'
            $sql = "INSERT INTO priv_avisos (
                        titulo, 
                        contenido, 
                        tipo, 
                        fecha_pub, 
                        id_info 
                    ) 
                    VALUES (
                        :titulo, 
                        :contenido, 
                        :tipo, 
                        NOW(), 
                        -- Subconsulta: Obtenemos el id_info usando el public_id del usuario
                        (SELECT i.id_info 
                         FROM priv_infousuario i 
                         JOIN priv_usuarios u ON i.id_usuario = u.id_usuario 
                         WHERE u.public_id = :user_public_id)
                    )";

            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':titulo', $data['titulo']);
            $stmt->bindParam(':contenido', $data['contenido']);
            $stmt->bindParam(':tipo', $data['tipo']);
            
            // El controlador nos manda el user_public_id (session['user_id'])
            $stmt->bindParam(':user_public_id', $data['user_public_id']); 

            return $stmt->execute();

        } catch (\PDOException $e) {
            error_log("Error SQL en createAviso: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAviso($id_aviso, $userPublicId)
    {
        try {
            // CORRECCIÓN 3: Validamos propiedad mediante id_info
            $sql = "DELETE FROM priv_avisos 
                    WHERE id_aviso = :id_aviso 
                    AND id_info = (
                        SELECT i.id_info 
                        FROM priv_infousuario i 
                        JOIN priv_usuarios u ON i.id_usuario = u.id_usuario 
                        WHERE u.public_id = :user_public_id
                    )";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_aviso', $id_aviso);
            $stmt->bindParam(':user_public_id', $userPublicId);

            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (\PDOException $e) {
            error_log("Error SQL en deleteAviso: " . $e->getMessage());
            return false;
        }
    }
}