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

public function getResidentInfo($userId)
    {
        // **LA CORRECCIÓN ESTÁ AQUÍ**: Añadimos i.id_info a la consulta.
        $stmt = $this->conn->prepare(
            'SELECT p.nombre AS privada_nombre, u.num_casa, i.nombres, i.id_info
             FROM priv_usuarios u 
             JOIN priv_privadas p ON u.id_privada = p.id_privada
             JOIN priv_infousuario i ON u.id_usuario = i.id_usuario
             WHERE u.id_usuario = :id_usuario'
        );
        $stmt->execute(['id_usuario' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el estatus de pago, el monto y la próxima fecha de pago del residente.
     *
     * @param int $userId El ID del usuario residente.
     * @param int $privadaId El ID de la privada del residente (desde la sesión).
     * @return array La información del estatus de pago.
     */
    public function getPaymentStatus($userId, $privadaId)
    {
        // --- 1. Obtener datos clave de la tabla de la privada ---
        $stmtPrivada = $this->conn->prepare(
            'SELECT monto_mensual_residente, diacorte FROM priv_privadas WHERE id_privada = :id_privada'
        );
        $stmtPrivada->execute(['id_privada' => $privadaId]);
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

        // --- Verificar el último pago del usuario ---
        $stmtPago = $this->conn->prepare(
            'SELECT fecha_pago FROM priv_pagos WHERE id_usuario = :id_usuario ORDER BY fecha_pago DESC LIMIT 1'
        );
        $stmtPago->execute(['id_usuario' => $userId]);
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