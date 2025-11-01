<?php

namespace App\Models\Resident;

class DashboardModel {
    public function getPaymentStats() {
        try {
            return "Modelo Dashboard, función GetPaymentStats, Models/Resident/Dashboard.php";
        }
        catch (\PDOException $e) {
            // En un caso real, aquí se manejaría el error (ej. log)
            // Por ahora, devolvemos un array vacío para evitar que la aplicación se rompa.
            return [];
        }
    }
}

