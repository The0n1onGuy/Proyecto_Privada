<?php

namespace App\Models\Admin;
use App\Core\Database;
use PDO;

class UtilityModel {

    public function obtenTodosPrivadas(){

        // Obtiene todas las privadas que están habilitadas "id_estatus = 1"

        $conn = Database::getConnection();
        $sql = "SELECT id_privada, nombre, public_id 
                FROM priv_privadas 
                WHERE id_estatus = '1'";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenDatosEstatus(){

        // Obten todos los estatus
        $conn = Database::getConnection();
        $stmt = $conn->query("SELECT estatus FROM priv_estatus");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenPrimDatosEstatus(){
        $conn = Database::getConnection();
        $sql = "SELECT estatus FROM priv_estatus WHERE id_estatus IN (?, ?)";
        $stmt = $conn->prepare($sql);
        // Ejecutamos la consulta pasando los IDs indirectamente por execute 
        $stmt->execute([1, 2]);
        // (FETCH_COLUMN dará un array simple respecto a la columna en este caso debe ser: ['Activo', 'Inactivo'])
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    public function obtenDatosColabRoles(){
        $conn = Database::getConnection();
        $sql = "SELECT rol FROM priv_roles WHERE id_rol IN (?, ?, ?,?)";
        $stmt = $conn->prepare($sql);
        // Ejecutamos la consulta pasando los IDs indirectamente por execute 
        $stmt->execute([1, 3, 4,5]);
        
        // (FETCH_COLUMN dará un array simple respecto a la columna)
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    /**
     * Verifica si un nombre de usuario ya existe en la base de datos.
     *
     * @param string $username Nombre de usuario a verificar
     * @return bool Retorna true si existe, false si no existe
     */
    public function verificarUsuario($username) {
        // ------------------------------------------------------------
        // Obtener la conexión a la base de datos
        // ------------------------------------------------------------
        // Se asume que existe una clase Database que gestiona la conexión PDO
        // Esto permite centralizar la configuración de la base de datos y reutilizar la conexión
        $conn = Database::getConnection(); // Usando la clase Database
        // ------------------------------------------------------------
        // Preparación de la consulta SQL
        // ------------------------------------------------------------
        // Se cuenta cuántos registros coinciden con el nombre de usuario proporcionado
        // Se utiliza un parámetro enlazado (:usuario) para prevenir inyecciones SQL
        $query = "SELECT COUNT(*) as total FROM priv_usuarios WHERE usuario = :usuario";
        $stmt = $conn->prepare($query);
        // Enlaza el valor de la variable $username al parámetro :usuario de la consulta
        // Esto asegura que PDO maneje correctamente los caracteres especiales y evite SQL Injection
        $stmt->bindParam(':usuario', $username);
        $stmt->execute();
        // Obtiene el resultado como un arreglo asociativo
        // Se espera que contenga un único campo 'total' con la cantidad de coincidencias encontradas
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        // ------------------------------------------------------------
        // Retornar el resultado de la verificación
        // ------------------------------------------------------------
        // Si el total es mayor a 0 significa que el usuario ya existe
        // Retorna true si existe, false si no existe
        return $resultado['total'] > 0;
    }
    // public function obtenDatosAdmin($userId)
    // {
    //     // **LA CORRECCIÓN ESTÁ AQUÍ**: Añadimos i.id_info a la consulta.
    //     // $conn = Database::getConnection(); // Usando la clase Database
    //     $sql = "SELECT estatus FROM priv_estatus WHERE id_estatus IN (?, ?)";
    //     $stmt = conn->prepare(
    //         'SELECT p.nombre AS privada_nombre, u.num_casa, i.nombres, i.id_info
    //          FROM priv_usuarios u 
    //          JOIN priv_privadas p ON u.id_privada = p.id_privada
    //          JOIN priv_infousuario i ON u.id_usuario = i.id_usuario
    //          WHERE u.id_usuario = :id_usuario'
    //     );
    //     $stmt->execute(['id_usuario' => $userId]);
    //     return $stmt->fetch(PDO::FETCH_ASSOC);
    // }
    public function obtenDatosAdmin($userId)
    {
        $conn = Database::getConnection(); // Usando la clase Database
        $sql = "SELECT p.nombre AS privada_nombre, u.num_casa, i.nombres, i.id_info
             FROM priv_usuarios u 
             JOIN priv_privadas p ON u.id_privada = p.id_privada
             JOIN priv_infousuario i ON u.id_usuario = i.id_usuario
             WHERE u.id_usuario = :id_usuario";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id_usuario' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
 * Verifica si un número de casa ya existe en la base de datos.
 *
 * Esta función se utiliza principalmente antes de asignar un nuevo número de casa
 * a un residente, para evitar duplicidad en los registros y mantener la integridad de los datos.
 *
 * @param string $numCasa Número de casa a verificar.
 * @return bool Retorna true si el número de casa ya existe en la base de datos, false si no existe.
 */
public function verificarNumCasa($numCasa) {
    // ------------------------------------------------------------
    // Obtener la conexión a la base de datos
    // ------------------------------------------------------------
    $conn = Database::getConnection();

    // ------------------------------------------------------------
    // Preparación de la consulta SQL
    // ------------------------------------------------------------
    // Se obtiene el id_usuario y se verifica si el usuario es propietario
    $sql = "SELECT pu.id_usuario
            FROM priv_usuarios pu
            INNER JOIN priv_infousuario pi ON pu.id_usuario = pi.id_usuario
            WHERE pu.num_casa = :num_casa
              AND pi.es_propietario = 1
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_casa', $numCasa);
    $stmt->execute();

    // ------------------------------------------------------------
    // Retornar el resultado de la verificación
    // ------------------------------------------------------------
    // Si se encuentra un registro significa que la casa ya está asignada a un propietario
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado !== false;
}

public function buscarPropietarioPorCasa($numCasa) {
    $conn = Database::getConnection();

    // --- Obtener los datos del propietario usando JOIN ---
    $sql = "SELECT pi.nombres, pi.apellido_p, pi.apellido_m
            FROM priv_infousuario pi
            INNER JOIN priv_usuarios pu ON pi.id_usuario = pu.id_usuario
            WHERE pu.num_casa = :num_casa
              AND pi.es_propietario = 1
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_casa', $numCasa);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}