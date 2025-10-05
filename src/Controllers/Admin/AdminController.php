<?php

namespace App\Controllers\Admin;

use App\Models\Admin\DashboardModel;
use App\Models\Admin\UsersModel;
use App\Models\Admin\ColaboradoresModel;

class AdminController
{
    public function showDashboard()
    {
        $dashboardModel = new DashboardModel();
        $paymentStats = $dashboardModel->getPaymentStats();
        $reportes = $dashboardModel->getReportes();
        $avisos = $dashboardModel->getAvisosD(); //Renombre la funcion a otra por conflictos de llamada en este codigo
        $data['reportes'] = $reportes;
        $data['avisos'] = $avisos;
        $data['paymentStatsJSON'] = json_encode($paymentStats);
        
        $assets['styles'] = ['/css/admin_dashboard.css'];
        $assets['scripts'] = [
            'https://cdn.jsdelivr.net/npm/chart.js',
        ];
        // $assets['scripts'] = ['/js/dashboardContent.js' ];

        $view_to_load = 'dashboard.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
        public function showUsers()
    {
        $usersModel = new UsersModel();
        $users = $usersModel->getusers();

        $data['colaboradores'] = $users;   
        
        $assets['styles'] = ['/css/admin_users.css'];
        $assets['scripts'] = ['/js/dataTables.js',];

        $view_to_load = 'users.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showPrivadas()
    {
        // $usersModel = new UsersModel();
        // $users = $usersModel->getusers();

        // $data['users'] = $users;   
        
        // $assets['styles'] = ['/css/admin_users.css'];
        // $assets['scripts'] = ['/js/dataTables.js',];

        $view_to_load = 'privadas.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showServicios()
    {
        // $usersModel = new UsersModel();
        // $users = $usersModel->getusers();

        // $data['users'] = $users;   
        
        // $assets['styles'] = ['/css/admin_users.css'];
        // $assets['scripts'] = ['/js/dataTables.js',];

        $view_to_load = 'servicios.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    
    public function showColaboradores()
    {
        $colaboradorModel = new ColaboradoresModel();
        $colaborador = $colaboradorModel->getColaboradores();
        $data['colaboradores'] = $colaborador;

        // Asumimos que tienes un modelo para privadas que obtiene todas las privadas.
        // Si no lo tienes, puedes crearlo, es muy sencillo.
        $data['Pcolaboradores'] = $colaboradorModel->getAllPrivadas();
        $data['roles'] = ['Administrador', 'Supervisor', 'Seguridad', 'Colaborador'];
        $data['estatus'] = ['Activo', 'Inactivo'];  
        $assets['styles'] = ['/css/admin_colaboradores.css'];
        $assets['scripts'] = ['/js/admin_colaboradores.js'];

        $view_to_load = 'colaboradores.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function updateColaborador()
    {
        // Security: Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['id_usuario']) || empty($input['nombres'])) {
                throw new Exception('Faltan datos requeridos.');
            }

            $colaboradorModel = new ColaboradoresModel();            
            $success = $colaboradorModel->updateColaborador($input);

            // 3. Send the JSON response
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Colaborador actualizado correctamente.']);
            } else {
                 throw new Exception('La actualización falló en el modelo.');
            }

        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
        }
        exit; 
    }
    public function showAvisos()
    {
        // $usersModel = new UsersModel();
        // $users = $usersModel->getusers();

        // $data['users'] = $users;   
        
        // $assets['styles'] = ['/css/admin_users.css'];
        // $assets['scripts'] = ['/js/dataTables.js',];

        $view_to_load = 'avisos.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showReportes()
    {
        // $usersModel = new UsersModel();
        // $users = $usersModel->getusers();

        // $data['users'] = $users;   
        
        // $assets['styles'] = ['/css/admin_users.css'];
        // $assets['scripts'] = ['/js/dataTables.js',];

        $view_to_load = 'reportes.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showConfigs()
    {
        // $usersModel = new UsersModel();
        // $users = $usersModel->getusers();

        // $data['users'] = $users;   
        
        // $assets['styles'] = ['/css/admin_users.css'];
        // $assets['scripts'] = ['/js/dataTables.js',];

        $view_to_load = 'configs.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function loadContent($view)
    {
        ob_start();

        $data = []; // Inicializamos el array de datos
        $assets = ['styles' => [], 'scripts' => []]; // Inicializamos el array de assets
        $view_file = basename($view, '.php');
        $view_path = __DIR__ . '/../../Views/Admin/sections/' . $view_file . '.php';

        if (!file_exists($view_path)) {
            ob_end_clean(); // Limpiamos el buffer de salida
            http_response_code(404);
            echo json_encode(['html' => 'Contenido no encontrado.']);
            return;
        }

        switch ($view_file) {
            case 'dashboard':
                $dashboardModel = new DashboardModel();
                $reportes = $dashboardModel->getReportes();
                $avisos = $dashboardModel->getAvisosD(); //Renombre la funcion a otra por conflictos de llamada en este codigo
                $data['reportes'] = $reportes;
                $data['avisos'] = $avisos;
                $data['paymentStatsJSON'] = json_encode($dashboardModel->getPaymentStats());
                $assets['styles'][] = '/css/admin_dashboard.css';
                $assets['scripts'][] = 'https://cdn.jsdelivr.net/npm/chart.js';
                break;
            case 'users':
                $usersModel = new UsersModel();
                $data['users'] = $usersModel->getUsers();
                $assets['styles'][] = '/css/admin_users.css';
                $assets['scripts'][] = '/js/dataTables.js';
                break;
            case 'colaboradores':
                
                $colaboradorModel = new ColaboradoresModel();
                $colaborador = $colaboradorModel->getColaboradores();
                $data['colaboradores'] = $colaborador;
                $data['Pcolaboradores'] = $colaboradorModel->getAllPrivadas();
                $data['roles'] = ['Administrador', 'Supervisor', 'Seguridad', 'Colaborador'];
                $data['estatus'] = ['Activo', 'Inactivo'];  
                $assets['styles'][] = '/css/admin_colaboradores.css';
                $assets['scripts'][] = '/js/admin_colaboradores.js';
                break;
        }

        extract($data);
        require $view_path; // La salida de este 'require' se guarda en el buffer.

        $html = ob_get_clean(); // Obtenemos el HTML del buffer y lo limpiamos.

        // Devolvemos una respuesta JSON
        header('Content-Type: application/json');
        echo json_encode(['html' => $html, 'assets' => $assets]);
    }

}

 