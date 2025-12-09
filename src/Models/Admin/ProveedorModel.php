<?php

namespace App\Models\Admin;
use App\Core\Database;
use PDO, Exception;

class ProveedorModel {
    private const ESTATUS_ACTIVO = 1;
    private const ESTATUS_INACTIVO = 2;
    private const NOVAL = 0;
    /**
     * Obtiene la lista de servicios y proveedores activos para una privada específica.
     * @param string $public_id_privada El UUID de la privada actual.
     */    
    public function obtenProveedor(string $public_id_privada){
        try {
            $conn = Database::getConnection();
            
            $sql = "
            SELECT 
                
                pps.public_id,
                
                -- Datos del Proveedor
                prov.nombre_empresa,
                prov.nombre_encargado,
                
                -- Datos del Servicio
                serv.nom_serv,
                
                -- Datos de Categoría (Opcional, para contexto)
                cat.nombre AS categoria,
                
                -- Estatus en la relación
                e.estatus
                
            FROM priv_privada_servicios pps
            
            JOIN priv_privadas p ON pps.id_privada_fk = p.id_privada
            JOIN priv_servicios serv ON pps.id_servicio_fk = serv.id_servicio
            JOIN priv_proveedor prov ON pps.id_proveedor_fk = prov.id_proveedor
            JOIN priv_servicios_categorias cat ON serv.id_categoria_fk = cat.id_categoria
            JOIN priv_estatus e ON pps.id_estatus = e.id_estatus

            WHERE p.public_id = :public_id";

            $stmt = $conn->prepare($sql);
            $stmt->execute([':public_id' => $public_id_privada]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error en obtener servicios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles completos de una asignación de servicio por su UUID.
     * @param string $public_id El UUID de la tabla puente (privada y servicios).
     */
    public function obtenServicioDetalles($public_id) {
        try {
            $conn = Database::getConnection();
            
            $sql = "
            SELECT 
                pps.fecha_asignacion,
                
                -- Datos Proveedor
                prov.id_proveedor,
                prov.nombre_empresa,
                prov.nombre_encargado,
                
                -- Datos Servicio
                serv.nom_serv,
                serv.precio_base,
                
                -- Categoría
                cat.nombre AS categoria
                
            FROM priv_privada_servicios pps
            JOIN priv_proveedor prov ON pps.id_proveedor_fk = prov.id_proveedor
            JOIN priv_servicios serv ON pps.id_servicio_fk = serv.id_servicio
            LEFT JOIN priv_servicios_categorias cat ON serv.id_categoria_fk = cat.id_categoria
            
            WHERE pps.public_id = :public_id
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute([':public_id' => $public_id]);
            $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$servicio) return false;

            // Obtener contactos del proveedor
            $id_proveedor = $servicio['id_proveedor'];
            $servicio['telefonos'] = $this->obtenTelefonosProveedor($id_proveedor);
            $servicio['correos'] = $this->obtenCorreosProveedor($id_proveedor);

            return $servicio;

        } catch (\PDOException $e) {
            error_log("Error obteniendo los detalles del servicio: " . $e->getMessage());
            return false;
        }
    }
    
    public function crearProveedor(array $data){
        $conn = Database::getConnection();
        $conn->beginTransaction();
        
        try {
            $sqlProv = "INSERT INTO priv_proveedor (public_id, nombre_empresa, nombre_encargado, id_servicio, id_estatus)
                        VALUES (
                            ,UUID()
                            ,:nombre_emp
                            ,:nombre_enc,
                            ,?
                            ,(SELECT id_estatus FROM priv_estatus WHERE estatus = :estatus)
                        )";
            
            $stmtProv = $conn->prepare($sqlProv);
            $stmtProv->execute([
                , ':nombre_emp' => $data['nombre_emp']
                , ':nombre_enc' => $data['nombre_enc']
                , SELF::NOVAL
                , ':estatus' => $data['estatus']
            ]);
            
            // Obtenemos el ID del servicio que acabamos de crear
            $id_prov = $conn->lastInsertId();

            //    Insertar los correos en `priv_corresprove` 
            if (!empty($data['correos'])) {
                $sqlMail = "INSERT INTO priv_corresprove (correo, id_proveedor, id_estatus) VALUES (?, ?, ?)";
                $stmtMail = $conn->prepare($sqlMail);
                foreach ($data['correos'] as $correo) {
                    $stmtMail->execute([$correo,$id_prov,self::ESTATUS_ACTIVO]);
                }
            }

            //    Insertar los telefonos en `priv_telprove` 
            if (!empty($data['telefonos'])) {
                $sqlPhone = "INSERT INTO priv_telprove (telefono, id_proveedor,id_estatus) VALUES (?, ?, ?)";
                $stmtPhone = $conn->prepare($sqlPhone);
                foreach ($data['telefonos'] as $telefono) {
                    $stmtPhone->execute([$telefono,$id_prov, self::ESTATUS_ACTIVO]);
                }
            }
            
            // Si todas las consultas fueron exitosas, confirma los cambios.
            $conn->commit();
            return true;

        } catch (Exception $e) {
            // Si algo falló, deshace todos los cambios.
            $conn->rollBack();
            // Lanza la excepción para que el controlador pueda manejarla.
            throw $e;
        }
    }

    // Obtener teléfonos del proveedor
    public function obtenTelefonosProveedor($id_proveedor) {
        $conn = Database::getConnection();
        $sql = "SELECT telefono FROM priv_telprove WHERE id_proveedor = ? AND id_estatus = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_proveedor, self::ESTATUS_ACTIVO]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener correos del proveedor
    public function obtenCorreosProveedor($id_proveedor) {
        $conn = Database::getConnection();
        $sql = "SELECT correo FROM priv_correoprove WHERE id_proveedor = ? AND id_estatus = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id_proveedor, self::ESTATUS_ACTIVO]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}