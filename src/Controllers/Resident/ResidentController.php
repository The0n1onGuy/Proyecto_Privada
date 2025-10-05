<?php

namespace App\Controllers\Resident;

use App\Models\User;

class ResidentController
{
    public function showDashboard()
    {
        echo "Bienvenido al panel de usuario.";
        require __DIR__ . '/../../Views/Resident/Dashboard.php';
    }
}