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
    /**
     * Procesa la creación de una nueva visita (POST)
     */
    public function createVisita()
    {
        header('Content-Type: application/json');

        // --- DEBUG: Inicio de la función ---
        error_log("[Visitas] Iniciando createVisita. POST data: " . print_r($_POST, true));
        error_log("[Visitas] Session user_id: " . ($_SESSION['user_id'] ?? 'NO SET'));

        // 1. Verificamos sesión
        if (!isset($_SESSION['user_id'])) {
            error_log("[Visitas] Error: Sesión no válida (user_id no existe).");
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
            return;
        }

        // 2. Mapeo de Estatus (Texto -> Número)
        // AJUSTA ESTOS VALORES SEGÚN TU BASE DE DATOS (ej. 1=Activo, 2=Inactivo)
        $estatusTexto = $_POST['estatus'] ?? 'Activo';
        $estatusNumerico = ($estatusTexto === 'Activo') ? 1 : 2; 

        // 3. Recibimos datos
        $data = [
            'nombre_visitante' => $_POST['nombre_visitante'] ?? '',
            'apellido_visitante' => $_POST['apellido_visitante'] ?? '',
            'tipo_visita' => $_POST['tipo_visita'] ?? '',
            'id_residente' => $_SESSION['user_id'], // Usamos el UUID del usuario logueado
            'observaciones' => $_POST['observaciones'] ?? '',
            'estatus' => $estatusNumerico // ¡ENVIAMOS EL NÚMERO!
        ];

        // --- DEBUG: Datos preparados ---
        error_log("[Visitas] Datos a enviar al modelo: " . print_r($data, true));

        // 4. Validación
        if (empty($data['nombre_visitante']) || empty($data['apellido_visitante']) || empty($data['tipo_visita'])) {
            error_log("[Visitas] Error: Faltan datos obligatorios.");
            echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
            return;
        }

        // 5. Llamada al modelo
        $visitasModel = new VisitasModel();
        $success = $visitasModel->createVisita($data);

        if ($success) {
            error_log("[Visitas] Éxito: Visita creada.");
            echo json_encode(['success' => true, 'message' => 'Visita registrada correctamente.']);
        } else {
            error_log("[Visitas] Error: El modelo devolvió false.");
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos. Revisa el log de errores.']);
        }
    }

    /**
     * Procesa la actualización de estatus (POST)
     */
    public function updateVisitaStatus()
    {
        header('Content-Type: application/json');

        // --- DEBUG: Inicio update ---
        // error_log("[Visitas] Iniciando updateVisitaStatus. POST: " . print_r($_POST, true));

        if (!isset($_POST['id_visita']) || !isset($_POST['estatus'])) {
            error_log("[Visitas] Error: Datos incompletos para update.");
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            return;
        }

        $id_visita = $_POST['id_visita'];
        $estatusTexto = $_POST['estatus'];
        
        // Conversión a Entero (Asumiendo 1=Activo, 2=Inactivo)
        // Si tu BD usa 0 para inactivo, cambia el 2 por 0.
        $estatusNumerico = ($estatusTexto === 'Activo') ? 1 : 2;

        // error_log("[Visitas] ID: $id_visita, Estatus Texto: $estatusTexto, Estatus Numérico: $estatusNumerico");

        $visitasModel = new VisitasModel();
        $success = $visitasModel->updateEstatusVisita($id_visita, $estatusNumerico);

        if ($success) {
            error_log("[Visitas] Update exitoso.");
            echo json_encode(['success' => true, 'message' => 'Estatus actualizado.']);
        } else {
            error_log("[Visitas] Error en update (Modelo devolvió false).");
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estatus.']);
        }
    }
    public function createAviso()
    {
        header('Content-Type: application/json');

        // 1. Seguridad: Verificar sesión
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['id_privada'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Sesión inválida.']);
            return;
        }

        // 2. Generar un public_id único para el nuevo aviso
        // Si tienes una función global de UUID o hash, úsala. Aquí un ejemplo simple:
        $avisoPublicId = bin2hex(random_bytes(16)); 
        // O si usas uniqid: $avisoPublicId = uniqid('aviso_', true);

        // 3. Preparar datos (Usando IDs de Sesión)
        $data = [
            'tipo' => $_POST['tipo'] ?? 'Aviso',
            'titulo' => $_POST['titulo'] ?? '',
            'contenido' => $_POST['contenido'] ?? '',
            'user_public_id' => $_SESSION['user_id'],       // UUID Usuario
            'privada_public_id' => $_SESSION['id_privada'], // UUID Privada
            'aviso_public_id' => $avisoPublicId             // UUID Nuevo Aviso
        ];

        if (empty($data['titulo']) || empty($data['contenido'])) {
            echo json_encode(['success' => false, 'message' => 'Título y contenido requeridos.']);
            return;
        }

        // 4. Ejecutar
        $avisosModel = new AvisosModel();
        $success = $avisosModel->createAviso($data);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Aviso publicado.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al guardar el aviso.']);
        }
    }

    public function deleteAviso()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || !isset($_POST['id_aviso'])) {
            echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
            return;
        }

        try {
            $id_aviso = (int)$_POST['id_aviso'];
            $userPublicId = $_SESSION['user_id'];

            $model = new AvisosModel();
            $success = $model->deleteAviso($id_aviso, $userPublicId);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Aviso eliminado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar (¿Es tu aviso?).']);
            }

        } catch (\Exception $e) {
            error_log("Error deleteAviso: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error inesperado.']);
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