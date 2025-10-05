<?php

namespace App\Controllers\Admin;

use App\Models\Admin\DashboardModel;
use App\Models\Admin\UsersModel;

class AdminControllerProporsal
{
    /**
     * This is our new private helper method. It gets all the necessary
     * data based on the view name and then loads the main panel.
     */
    private function renderPage(string $viewName)
    {
        $data = [];
        $assets = ['styles' => [], 'scripts' => []];

        // This switch statement centralizes the logic for fetching data
        // and setting assets for each specific view.
        switch ($viewName) {
            case 'dashboard':
                $dashboardModel = new DashboardModel();
                $data['paymentStatsJSON'] = json_encode($dashboardModel->getPaymentStats());
                $data['reportes'] = $dashboardModel->getReportes();
                $data['avisos'] = $dashboardModel->getAvisosD();
                $assets['styles'][] = '/css/admin_dashboard.css';
                $assets['scripts'][] = 'https://cdn.jsdelivr.net/npm/chart.js';
                break;

            case 'users':
                $usersModel = new UsersModel();
                $data['users'] = $usersModel->getUsers();
                $assets['styles'][] = '/css/admin_users.css';
                $assets['scripts'][] = '/js/dataTables.js';
                break;
            
            case 'privadas':
                // Add logic for 'privadas' here in the future
                break;

            case 'servicios':
                // Add logic for 'servicios' here in the future
                break;

            case 'colaboradores':
                 // Add logic for 'colaboradores' here in the future
                break;

            case 'avisos':
                 // Add logic for 'avisos' here in the future
                break;
            
            case 'reportes':
                // Add logic for 'reportes' here in the future
                break;
        }

        // This is the repetitive part that is now handled in one place.
        $view_to_load = $viewName . '.php';
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }

    // --- Your public methods are now clean one-liners ---

    public function showDashboard()
    {
        $this->renderPage('dashboard');
    }

    public function showUsers()
    {
        $this->renderPage('users');
    }

    public function showPrivadas()
    {
        $this->renderPage('privadas');
    }

    public function showServicios()
    {
        $this->renderPage('servicios');
    }
    
    public function showColaboradores()
    {
        $this->renderPage('colaboradores');
    }
    
    public function showAvisos()
    {
        $this->renderPage('avisos');
    }

    public function showReportes()
    {
        $this->renderPage('reportes');
    }

    // Your loadContent function can also be slightly optimized
    // to reuse the data-fetching logic, but it's good as is.
    public function loadContent($view)
    {
        // ... your existing loadContent logic ...
    }
}