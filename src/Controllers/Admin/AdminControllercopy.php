<?php

namespace App\Controllers\Admin;

use App\Models\Admin\UtilityModel;
use App\Models\Admin\DashboardModel;
use App\Models\Admin\ResidentsModel;
use App\Models\Admin\ColaboradoresModel;
use App\Core\SessionVerifier;
use App\Models\SessionDataModel;
use Exception;

class AdminControllercopy
{
    public function showPrivateSelection()
    {
        $utilityModel = new UtilityModel();
        $data['privadas'] = $utilityModel->obtenTodosPrivadas();
        extract($data);
        require __DIR__ . '/../../Views/Admin/SelectPrivate.php';
    }

    // --- FUNCIÓN CORREGIDA ---
    private function cargarDatosDelPanel(): array
    {
        $utilityModel = new UtilityModel();
        $data['todas_las_privadas'] = $utilityModel->obtenTodosPrivadas();
        
        // 1. Leemos el PUBLIC_ID (UUID) de la sesión
        $public_id_actual = $_SESSION['public_id_privada'] ?? null; // <--- CAMBIO
        
        $data['privada_actual'] = null;
        if (!empty($data['todas_las_privadas']) && $public_id_actual) {
            foreach ($data['todas_las_privadas'] as $privada) {
                // 2. Comparamos el PUBLIC_ID de la DB con el PUBLIC_ID de la sesión
                if ($privada['public_id'] == $public_id_actual) { // <--- CAMBIO
                    $data['privada_actual'] = $privada;
                    break;
                }
            }
        }
        return $data;
    }

    public function showDashboard()
    {
        // Esta verificación ya estaba correcta
        if (!isset($_SESSION['public_id_privada'])) {
            header(header: 'Location: /admin/select-private');
            exit;
        }
        $data = $this->cargarDatosDelPanel(); // Esta función ahora es correcta
        $dashboardModel = new DashboardModel();
        $privadaModel = new UtilityModel();
        
        $reportes = $dashboardModel->getReportes();
        $avisos = $dashboardModel->getAvisosD();
        $data['reportes'] = $reportes;
        $data['avisos'] = $avisos;
        
        // Esta línea ya estaba correcta
        $data['paymentStatsJSON'] = json_encode($dashboardModel->obtenPagosStat($_SESSION['public_id_privada']));

        $assets['styles'] = ['/css/Admin/admin_dashboard.css'];                
        $assets['scripts'] = ['https://cdn.jsdelivr.net/npm/chart.js'];
        $view_to_load = 'dashboard.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }

    // --- FUNCIÓN CORREGIDA ---
    public function showResidents()
    {
        $data = $this->cargarDatosDelPanel();
        
        // Revisa si hay un public_id_privada, de otra forma rebotalo
        if (!isset($_SESSION['public_id_privada'])) { // <--- CAMBIO
            header(header: 'Location: /admin/select-private');
            exit;
        }

        $filter = $_GET['filter'] ?? 'owners';
        $residentsModel = new ResidentsModel();
        $utilityModel = new UtilityModel();
        
        // Pasamos el public_id al modelo (tu modelo ya lo esperaba así)
        $residents = $residentsModel->getAllResidents($_SESSION['public_id_privada'], $filter); // <--- CAMBIO

        $data['residents'] = $residents;
        $data['Presidentes'] = $utilityModel->obtenTodosPrivadas();
        $data['residentesEstados'] = $utilityModel->obtenPrimDatosEstatus();
        $data['currentFilter'] = $filter;
        
        $assets['styles'] = ['/css/Utilities/DataTables.css', '/css/Admin/admin_residents.css'];
        $assets['scripts'] = ['/js/admin_residentes.js'];

        $view_to_load = 'residents.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    
    public function getResidentData($id)
    {
        // ... (Esta función no usa el id_privada, así que está bien) ...
        header('Content-Type: application/json');
        $residentsModel = new ResidentsModel();
        $resident = $residentsModel->getResidentById($id);
        if ($resident) {
            echo json_encode(['success' => true, 'data' => $resident]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Residente no encontrado.']);
        }
        exit;
    }

    // --- FUNCIÓN CORREGIDA (PARCIAL) ---
    public function operacion_Residentes($caso){
        $casoS = $caso;
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        switch ($casoS){
            case 1: // CREAR UN RESIDENTE
                // ... (código sin cambios) ...
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                try {
                    if (empty($data['username']) || empty($data['password']) || empty($data['nombres'])) {
                        throw new Exception('Faltan datos requeridos.');
                    }
                    $residentsModel = new ResidentsModel(); 
                    $success = $residentsModel->creaResidente($data); 
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Colaborador creado exitosamente.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al crear el residente.']);
                    }
                } catch (Exception $e) {
                    http_response_code(500); 
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }
                exit;
            case 2: // ACTUALIZAR UN RESIDENTE
                // ... (código sin cambios) ...
                $data = $_POST;
                $residentsModel = new ResidentsModel();
                $success = $residentsModel->actualizaResident($data);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Residente act correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar el residente.']);
                }
                exit;
                
            case 3: // ELIMINAR UN RESIDENTE
                // ... (código sin cambios) ...
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                try {
                    if (empty($data['id_info'])) {
                        throw new Exception('No se proporcionó el ID del residente a eliminar.');
                    }    
                    $residentsModel = new ResidentsModel(); 
                    $success = $residentsModel->eliminaResidente($data['id_info']);
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Colaborador eliminado exitosamente.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al eliminar el residente.']);
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }
                exit;
            case 4: // CREAR UN RESIDENTE NO PROPIETARIO
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                try {
                    // Pasamos el PUBLIC_ID de la sesión al modelo
                    $data['public_id_privada'] = $_SESSION['public_id_privada'] ?? null; // <--- CAMBIO
                    
                    if (empty($data['nombres']) || empty($data['num_casa']) || empty($data['public_id_privada'])) { // <--- CAMBIO
                        throw new Exception('Faltan datos requeridos (nombre, casa o privada).');
                    }

                    // *** NOTA IMPORTANTE ***
                    // Tu modelo 'creaResidenteExtra' ahora debe estar preparado
                    // para recibir 'public_id_privada' (un UUID) y buscar
                    // el 'id_privada' numérico internamente antes de hacer el INSERT.
                    
                    $residentsModel = new ResidentsModel(); 
                    $success = $residentsModel->creaResidenteExtra($data);
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Colaborador creado exitosamente.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al crear el residente.']);
                    }

                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }
                exit;
        }
    }
    
    
    public function showPrivadas()
    {
        // ... (código sin cambios) ...
        $view_to_load = 'privadas.php';
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showServicios()
    {
        // ... (código sin cambios) ...
        $assets['styles'] = ['/css/Admin/admin_servicios.css'];
        $assets['scripts'] = ['/js/Admin/admin_servicios.js' , '/js/Admin/admin_proveedor.js'];
        $view_to_load = 'servicios.php';
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    
    // --- FUNCIÓN CORREGIDA ---
    public function showColaboradores()
    {
        $data = $this->cargarDatosDelPanel();
        
        // Revisa si hay un public_id_privada, de otra forma rebotalo
        if (!isset($_SESSION['public_id_privada'])) { // <--- CAMBIO
            header(header: 'Location: /admin/select-private');
            exit;
        }
        
        $colaboradorModel = new ColaboradoresModel();
        $utilityModel = new UtilityModel();
        $data['roles'] = $utilityModel->obtenDatosColabRoles(); // Obtenemos roles primero
        
        // Pasamos el public_id y los roles (tu modelo ya lo esperaba así)
        $colaboradores = $colaboradorModel->obtenColaboradores($_SESSION['public_id_privada'], $data['roles']); // <--- CAMBIO
        
        // Ya no es necesario json_encode, la vista lo recibe como array PHP
        $data['colaboradores'] = $colaboradores; // <--- CAMBIO
        $data['Pcolaboradores'] = $utilityModel->obtenTodosPrivadas();
        $data['colabestatus'] = $utilityModel->obtenPrimDatosEstatus();
        
        $assets['styles'][] = '/css/Admin/admin_colaboradores.css';
        $assets['scripts'] = ['/js/Admin/admin_colaboradoresAnadir.js' , '/js/Admin/admin_colaboradores.js'];

        $view_to_load = 'colaboradores.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }

    public function operacion_Colaborador($caso){
        // ... (Esta función no usa el id_privada, así que está bien) ...
        $casoS = $caso;
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        switch ($casoS){
            case 1: // CREAR O VERIFICAR UN COLABORADOR
            // ... (código sin cambios) ...
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            try {
                $utilityModel = new UtilityModel();
                $colaboradorModel = new ColaboradoresModel();
                if (!empty($data['verificar']) && $data['verificar'] === true) {
                    if (empty($data['username'])) {
                        throw new Exception('No se proporcionó el nombre de usuario.');
                    }
                    $existe = $utilityModel->verificarUsuario($data['username']);
                    echo json_encode(['existe' => $existe]);
                    exit;
                }
                if (empty($data['username']) || empty($data['password']) || empty($data['nombres'])) {
                    throw new Exception('Faltan datos requeridos.');
                }
                if ($utilityModel->verificarUsuario($data['username'])) {
                    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado.']);
                    exit;
                }
                $success = $colaboradorModel->creaColaborador($data);
                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Colaborador creado exitosamente.']);
                } else {
                    throw new Exception('No se pudo crear el colaborador.');
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
            }
            exit;

            case 2: // ACTUALIZAR UN COLABORADOR
            // ... (código sin cambios) ...
                try {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (empty($input['id_usuario']) || empty($input['nombres'])) {
                        throw new Exception('Faltan datos requeridos.');
                    }
                    $colaboradorModel = new ColaboradoresModel();            
                    $success = $colaboradorModel->actualizaColaborador($input);
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Colaborador actualizado correctamente.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar el colaborador.']);
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }
                exit;
                
            case 3: // ELIMINAR UN COLABORADOR
            // ... (código sin cambios) ...
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                try {
                    if (empty($data['id_usuario'])) {
                        throw new Exception('No se proporcionó el ID del colaborador a eliminar.');
                    }
                    $colaboradorModel = new ColaboradoresModel();
                    $success = $colaboradorModel->eliminarColaborador($data['id_usuario']);
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Colaborador eliminado exitosamente.']);
                    } else {
                        throw new Exception('No se pudo eliminar el colaborador.');
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }
                exit;
        }
    }
    
    public function showAvisos()
    {
        // ... (código sin cambios) ...
        $data = $this->cargarDatosDelPanel();
        $view_to_load = 'avisos.php';
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showConfigs()
    {
        // ... (código sin cambios) ...
        $assets['styles'][] = '/css/Admin/config.css';
        $assets['scripts'] = ['/js/Admin/admin_config.js'];
        $view_to_load = 'configs.php';
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function procesa_Modal_Configuracion($modalName){
        // ... (código sin cambios) ...
        $view_path = __DIR__ . '/../../Views/Admin/sections/sectionsconfig/';
        $file_to_load = '';
        switch ($modalName) {
            case 'editar-perfil':
                $file_to_load = $view_path . 'editperfil.php';
                break;
            default:
                http_response_code(404);
                echo "Modal no encontrado.";
                exit;
        }
        if (file_exists($file_to_load)) {
            require $file_to_load;
        } else {
            http_response_code(404);
            echo "Archivo de modal no encontrado: " . $file_to_load;
        }
        exit;
    }
    
    //
    // Esta función ya estaba CORRECTAMENTE MIGRADA
    // La dejo tal cual porque ya usaba public_id_privada
    //
    public function loadContent($view)
    {
        $sessionModel = new SessionDataModel(); 
        $verifier = new SessionVerifier($sessionModel);
        $keys_to_check = ['user_id'];
        
        $view_file = basename($view, '.php');
        $views_que_necesitan_privada = ['residents', 'colaboradores', 'dashboard']; 
        
        if (in_array($view_file, $views_que_necesitan_privada)) {
            $keys_to_check[] = 'public_id_privada'; // Correcto
        }

        if (!$verifier->verify($keys_to_check)) {
            http_response_code(401); 
            echo json_encode([
                'success' => false, 
                'message' => 'Sesión inválida o expirada.',
                'errors' => $verifier->getErrors()
            ]);
            exit; 
        }

        $data = $this->cargarDatosDelPanel(); // Ahora esta función es correcta
        ob_start();
        $privada_info = [
            'name' => 'Seleccionar Privada',
            'image' => '/images/default_privada.png'
        ];
        if (isset($data['privada_actual']) && $data['privada_actual']) {
            $privada_info['name'] = htmlspecialchars($data['privada_actual']['nombre']);
            $nombre_archivo_img = strtolower(str_replace(' ', '_', $data['privada_actual']['nombre'])) . '.jpg';
            $privada_info['image'] = "/images/privadas/" . $nombre_archivo_img;
        }
        $data = [];
        $assets = ['styles' => [], 'scripts' => []];
        $view_file = basename($view, '.php');
        $view_path = __DIR__ . '/../../Views/Admin/sections/' . $view_file . '.php';

        if (!file_exists($view_path)) {
            ob_end_clean();
            http_response_code(404);
            echo json_encode(['html' => 'Contenido no encontrado.']);
            return;
        }
        
        switch ($view_file) {
            case 'dashboard':
                $dashboardModel = new DashboardModel();
                $privadaModel = new UtilityModel();
                $reportes = $dashboardModel->getReportes();
                $avisos = $dashboardModel->getAvisosD();
                $data['reportes'] = $reportes;
                $data['avisos'] = $avisos;
                $data['paymentStatsJSON'] = json_encode($dashboardModel->obtenPagosStat($_SESSION['public_id_privada'])); // Correcto
                $assets['styles'] = ['/css/Admin/admin_dashboard.css'];                
                $assets['scripts'] = ['https://cdn.jsdelivr.net/npm/chart.js'];
                break;
            case 'residents':
                $filter = $_GET['filter'] ?? 'owners';
                $residentsModel = new ResidentsModel();
                $utilityModel = new UtilityModel();
                $data['residents'] = $residentsModel->getAllResidents($_SESSION['public_id_privada'], $filter); // Correcto
                $data['Presidentes'] = $utilityModel->obtenTodosPrivadas();
                $data['residentesEstados'] = $utilityModel->obtenPrimDatosEstatus();
                $data['currentFilter'] = $filter;
                $assets['styles'][] = '/css/Utilities/DataTables.css';
                $assets['styles'][] = '/css/Admin/admin_residents.css';
                $assets['scripts'] = ['/js/Admin/admin_residentes.js'];
                break;
            case 'colaboradores':
                $colaboradorModel = new ColaboradoresModel();
                $utilityModel = new UtilityModel();
                $data['roles'] = $utilityModel->obtenDatosColabRoles();
                $colaborador = $colaboradorModel->obtenColaboradores($_SESSION['public_id_privada'], $data['roles']); // Correcto
                $data['colaboradores'] = $colaborador;
                $data['Pcolaboradores'] = $utilityModel->obtenTodosPrivadas();
                $data['colabestatus'] = $utilityModel->obtenPrimDatosEstatus();
                $assets['styles'][] = '/css/Admin/admin_colaboradores.css';
                $assets['scripts'] = ['/js/Admin/admin_colaboradoresAnadir.js' , '/js/Admin/admin_colaboradores.js'];
                break;
            case 'servicios':
                $assets['styles'] = ['/css/Admin/admin_servicios.css'];
                $assets['scripts'] = ['/js/Admin/admin_servicios.js' , '/js/Admin/admin_proveedor.js'];
                break;
            case 'configs':
                $assets['styles'][] = '/css/Admin/config.css';
                $assets['scripts'] = ['/js/Admin/admin_config.js'];
                break;
        }

        extract($data);
        require $view_path;

        $html = ob_get_clean();

        header('Content-Type: application/json');
        echo json_encode(['html' => $html, 'assets' => $assets]);
    }
}