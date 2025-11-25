<?php

namespace App\Models\Resident;

use App\Core\Database;
use PDO;
use DateTime;

class StartModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

public function getResidentInfo($userPublicId)
    {
        // El parámetro $userPublicId es el $_SESSION['user_id'] (public_id)
        
        $stmt = $this->conn->prepare(
            'SELECT 
                 p.nombre AS privada_nombre, 
                 u.num_casa, 
                 i.nombres, 
                 i.public_id AS id_info  -- ¡LA CORRECCIÓN ESTÁ AQUÍ!
             FROM priv_usuarios u 
             JOIN priv_privadas p ON u.id_privada = p.id_privada
             JOIN priv_infousuario i ON u.id_usuario = i.id_usuario
             WHERE u.public_id = :user_public_id'
        );
        $stmt->execute(['user_public_id' => $userPublicId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el estatus de pago, el monto y la próxima fecha de pago del residente.
     *
     * @param int $userPublicId El ID pública del usuario residente.
     * @param int $privadaPublicId El ID pública de la privada del residente (desde la sesión).
     * @return array La información del estatus de pago.
     */
    public function getPaymentStatus($userPublicId, $privadaPublicId)
        {
            // --- 1. Obtener datos clave de la tabla de la privada ---
            $stmtPrivada = $this->conn->prepare(
                'SELECT monto_mensual_residente, diacorte FROM priv_privadas WHERE public_id = :public_id'
            );
            $stmtPrivada->execute(['public_id' => $privadaPublicId]);
            $privadaInfo = $stmtPrivada->fetch(PDO::FETCH_ASSOC);

            if (!$privadaInfo) {
                return [
                    'status_class' => 'pendiente',
                    'status_text' => 'Información no disponible',
                    'next_payment_date' => 'Contactar a administración',
                    'next_payment_amount' => null 
                ];
            }

            $montoMensual = $privadaInfo['monto_mensual_residente'];
            $diaCorte = $privadaInfo['diacorte'];

            // --- Calcular la próxima fecha de pago ---
            $hoy = new DateTime();
            $fechaCorteEsteMes = new DateTime($hoy->format('Y-m-') . $diaCorte);

            $proximaFechaPago = clone $fechaCorteEsteMes;
            if ($hoy > $fechaCorteEsteMes) {
                $proximaFechaPago->modify('+1 month');
            }

            // --- 2. Verificar el último pago del usuario ---
            
            // ¡¡LA CORRECCIÓN ESTÁ AQUÍ!!
            // La columna en priv_pagos que referencia al usuario también se llama 'public_id'.
        $stmtPago = $this->conn->prepare(
            'SELECT fecha_pago 
             FROM priv_pagos 
             WHERE id_usuario = (SELECT id_usuario FROM priv_usuarios WHERE public_id = :user_public_id) 
             ORDER BY fecha_pago DESC LIMIT 1' // <--- CAMBIO
        );
            // El parámetro debe coincidir con el placeholder
            $stmtPago->execute(['user_public_id' => $userPublicId]); // <--- CAMBIO
            $ultimoPago = $stmtPago->fetch(PDO::FETCH_ASSOC);

        if (!$ultimoPago) {
            return [
                'status_class' => 'pendiente',
                'status_text' => 'Primer Pago Pendiente',
                'next_payment_date' => $proximaFechaPago->format('Y-m-d'),
                'next_payment_amount' => $montoMensual
            ];
        }

        $fechaUltimoPago = new DateTime($ultimoPago['fecha_pago']);
        
        $fechaLimiteAnterior = clone $proximaFechaPago;
        $fechaLimiteAnterior->modify('-1 month');

        if ($fechaUltimoPago >= $fechaLimiteAnterior) {
            return [
                'status_class' => 'al-corriente',
                'status_text' => 'Al Corriente',
                'next_payment_date' => $proximaFechaPago->format('Y-m-d'),
                'next_payment_amount' => $montoMensual
            ];
        } else {
            return [
                'status_class' => 'vencido',
                'status_text' => 'Pago Vencido',
                'next_payment_date' => 'Inmediato',
                'next_payment_amount' => $montoMensual
            ];
        }
    }

    public function getAdminContacts()
    {
        // Esta función se mantiene sin cambios.
        $stmtPhones = $this->conn->query(
            'SELECT t.telefono FROM priv_telusuario t
             JOIN priv_infousuario i ON t.id_info = i.id_info
             JOIN priv_usuarios u ON i.id_usuario = u.id_usuario
             WHERE u.id_rol = 1 AND t.id_estatus = 1'
        );
        $telefonos = $stmtPhones->fetchAll(PDO::FETCH_COLUMN);

        $stmtEmails = $this->conn->query(
            'SELECT c.correo FROM priv_corresusuario c
             JOIN priv_infousuario i ON c.id_info = i.id_info
             JOIN priv_usuarios u ON i.id_usuario = u.id_usuario
             WHERE u.id_rol = 1 AND c.id_estatus = 1'
        );
        $correos = $stmtEmails->fetchAll(PDO::FETCH_COLUMN);

        return [
            'telefonos' => $telefonos,
            'correos' => $correos,
        ];
    }
}