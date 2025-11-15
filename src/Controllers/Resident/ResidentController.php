<?php

namespace App\Controllers\Resident;

use App\Models\Resident\StartModel;
use App\Models\Resident\ResidentsModel;
use App\Models\Resident\AvisosModel;
use App\Models\Resident\VisitasModel;
use App\Core\SessionVerifier;
use App\Models\SessionDataModel;
class ResidentController
{
public function showStart()
    {
        $startModel = new StartModel();
        $avisosModel = new AvisosModel();  

        $userId = $_SESSION['user_id'];
        $privadaId = $_SESSION['id_privada'];
        $residentInfo = $startModel->getResidentInfo($userId);
        $data['avisos'] = $avisosModel->getAllAvisos($privadaId);

        $_SESSION['user_nombre'] = $residentInfo['nombres'] ?? 'Residente';
        $data['privada_nombre'] = $residentInfo['privada_nombre'] ?? 'N/A';
        $data['house_number'] = $residentInfo['num_casa'] ?? 'N/A';
        $data['payment_status'] = $startModel->getPaymentStatus($userId, $privadaId);
        $data['admin_contacts'] = $startModel->getAdminContacts();
        
        $assets['styles'] = ['/css/Resident/start.css'];
        $assets['styles'][] = '/css/Resident/avisos.css';
        $assets['scripts'] = ['/js/Resident/start.js'];
        $view_to_load = 'start.php'; 
        require __DIR__ . '/../../Views/Resident/Panel.php';
    }
        public function showResidents()
    {   
        $filter = $_GET['filter'] ?? 'owners';
        $residentsModel = new ResidentsModel();
        $residents = $residentsModel->getAllResidents($_SESSION['id_privada'], $filter);
        $data['residents'] = $residents;
        $data['currentFilter'] = $filter;

        $assets['styles'] = ['/css/Utilities/DataTables.css'];
        $assets['styles'][] = ['/css/Resident/residents.css'];
        $assets['scripts'] = ['/js/Resident/residents.js'];
        $view_to_load = 'residents.php';
        require __DIR__ . '/../../Views/Resident/Panel.php';
    }
public function showAvisos()
    {
        $avisosModel = new AvisosModel();
        $id_privada = $_SESSION['id_privada'];
        $data['avisos'] = $avisosModel->getAllAvisos($id_privada);

        $assets['styles'] = ['/css/Resident/avisos.css'];
        $assets['scripts'] = ['/js/Resident/avisos.js'];
        $view_to_load = 'avisos.php';
         require __DIR__ . '/../../Views/Resident/Panel.php';
    }
    public function showVisitas()
    {
        $visitasModel = new VisitasModel();
        $data['visitas'] = $visitasModel->getAllVisitas($userId = $_SESSION['user_id']);
        
        $assets['styles'] = ['/css/Resident/visitas.css'];
        $assets['scripts'] = ['/js/Resident/visitas.js'];
        $view_to_load = 'visitas.php';
        require __DIR__ . '/../../Views/Resident/Panel.php';
    }
public function createAviso()
    {
        header('Content-Type: application/json');

        // **LA CORRECCIÓN ESTÁ AQUÍ**: Nos aseguramos de que id_info exista en la sesión.
        // Si no existe, lo buscamos antes de continuar.
        if (!isset($_SESSION['id_info'])) {
            $startModel = new StartModel();
            $userInfo = $startModel->getResidentInfo($_SESSION['user_id']);
            $_SESSION['id_info'] = $userInfo['id_info'] ?? null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_info'])) {
            $data = [
                'tipo' => $_POST['tipo'] ?? 'Aviso',
                'titulo' => $_POST['titulo'] ?? '',
                'contenido' => $_POST['contenido'] ?? '',
                'id_info' => $_SESSION['id_info'],
                'id_privada' => $_SESSION['id_privada']
            ];

            if (empty($data['titulo']) || empty($data['contenido'])) {
                echo json_encode(['success' => false, 'message' => 'El título y el contenido no pueden estar vacíos.']);
                return;
            }

            $avisosModel = new AvisosModel();
            $success = $avisosModel->createAviso($data);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Aviso creado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el aviso en la base de datos.']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'No se pudo verificar la identidad del usuario. Intente recargar la página.']);
        }
    }
    public function deleteAviso()
    {
        // Encabezado de respuesta JSON
        header('Content-Type: application/json');

        // Verificación de seguridad: ¿El usuario está logueado?
        // (Asumo que tu constructor o un middleware ya inicia la sesión)
        if (!isset($_SESSION['id_info']) || !isset($_POST['id_aviso'])) {
            echo json_encode(['success' => false, 'message' => 'Solicitud inválida o sesión expirada.']);
            return;
        }

        try {
            $id_aviso = (int)$_POST['id_aviso'];
            $id_info_session = (int)$_SESSION['id_info']; // ID de la sesión

            $model = new AvisosModel();
            
            // Pasamos ambos IDs al modelo para la eliminación segura
            $success = $model->deleteAviso($id_aviso, $id_info_session);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'El aviso ha sido eliminado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el aviso. Es posible que no seas el autor o ya fue eliminado.']);
            }

        } catch (\Exception $e) {
            // Manejo de errores
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado.']);
        }
    }

// Reemplaza toda la función loadContent con esta:
    public function loadContent($view)
    {
        // --- INICIO DE LA MODIFICACIÓN (Versión con SessionVerifier) ---
        
        // 1. Instanciamos el verificador
        $sessionModel = new SessionDataModel(); 
        $verifier = new SessionVerifier($sessionModel);

        // 2. Un residente siempre necesita ambas claves
        $keys_to_check = ['user_id', 'id_privada'];

        // 3. Verificamos
        if (!$verifier->verify($keys_to_check)) {
            http_response_code(401); // 401 Unauthorized
            echo json_encode([
                'success' => false, 
                'message' => 'Sesión inválida o expirada.',
                'errors' => $verifier->getErrors()
            ]);
            exit; 
        }
        // --- FIN DE LA MODIFICACIÓN ---

        ob_start();

        $data = []; // Inicializamos el array de datos
        $assets = ['styles' => [], 'scripts' => []]; // Inicializamos el array de assets
        $view_file = basename($view, '.php');
        $view_path = __DIR__ . '/../../Views/Resident/sections/' . $view_file . '.php';

        if (!file_exists($view_path)) {
            ob_end_clean(); // Limpiamos el buffer de salida
            http_response_code(404);
            echo json_encode(['html' => 'Contenido no encontrado.']);
            return;
        }

        switch ($view_file) {
            case 'start':
                // --- Lógica añadida ---
                $startModel = new StartModel();
                $avisosModel = new AvisosModel();
                $userId = $_SESSION['user_id'];
                $privadaId = $_SESSION['id_privada'];

                // Obtenemos toda la información requerida por la vista start.php
                $residentInfo = $startModel->getResidentInfo($userId);
                $data['avisos'] = $avisosModel->getAllAvisos($privadaId);
                
                // Hacemos que las variables estén disponibles para la vista que se va a cargar.
                // Es importante usar los mismos nombres de variables que en showStart.
                $privada_nombre = $residentInfo['privada_nombre'] ?? 'N/A';
                $house_number = $residentInfo['num_casa'] ?? 'N/A';
                $payment_status = $startModel->getPaymentStatus($userId, $privadaId);
                $admin_contacts = $startModel->getAdminContacts();
                $assets['styles'] = ['/css/Resident/start.css'];
                $assets['styles'][] = '/css/Resident/avisos.css';
                $assets['scripts'] = ['/js/Resident/start.js'];
                break;
            case 'residents':
                $residentsModel = new ResidentsModel();
                $filter = $_GET['filter'] ?? 'owners';
                $residents = $residentsModel->getAllResidents($_SESSION['id_privada'], $filter);
                $data['residents'] = $residents;
                $data['currentFilter'] = $filter;
                $assets['styles'] = ['/css/Utilities/DataTables.css'];
                $assets['styles'][] = ['/css/Resident/residents.css'];
                $assets['scripts'] = ['/js/Resident/residents.js'];
                
                break;
            case 'avisos':
                $avisosModel = new AvisosModel();
                $data['avisos'] = $avisosModel->getAllAvisos($_SESSION['id_privada']);
                $assets['styles'][] = '/css/Resident/avisos.css';
                $assets['scripts'][] = '/js/Resident/avisos.js';
                break;
            case 'visitas':
                $visitasModel = new VisitasModel();
                $data['visitas'] = $visitasModel->getAllVisitas($userId = $_SESSION['user_id']);
                $assets['styles'][] = '/css/Resident/visitas.css';
                $assets['scripts'][] = '/js/Resident/visitas.js';
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