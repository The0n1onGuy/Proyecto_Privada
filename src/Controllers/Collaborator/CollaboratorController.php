<?php

namespace App\Controllers\Collaborator;

use App\Models\SessionDataModel;
use App\Core\SessionVerifier;
use App\Models\Collaborator\ScheduleModel;
use Exception;

class CollaboratorController
{

/**
     * Método auxiliar para cargar datos del calendario (usado en la carga inicial)
     */
    private function cargarDatosSchedule(): array
    {
        $fecha_inicio = date('Y-m-01');
        $fecha_fin = date('Y-m-t'); // Último día del mes actual
        
        $userPublicId = null;
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 5) { 
            $userPublicId = $_SESSION['user_id'];
        }

        $scheduleModel = new ScheduleModel();
        $summaryData = $scheduleModel->getMonthlyActivitySummary($fecha_inicio, $fecha_fin, $userPublicId);

        $calendarData = [];
        
        // Reestructuramos para que JS lo lea fácil
        foreach ($summaryData as $row) {
            $date = $row['fecha_programada'];
            $status = strtolower($row['nombre_estatus']);
            
            // Asignar colores
            $colorClass = 'event-default';
            if (strpos($status, 'pendiente') !== false) $colorClass = 'event-warning'; // Amarillo
            if (strpos($status, 'completada') !== false) $colorClass = 'event-success'; // Verde
            if (strpos($status, 'cancelada') !== false) $colorClass = 'event-danger';  // Rojo
            if (strpos($status, 'proceso') !== false) $colorClass = 'event-info';    // Azul

            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [];
            }
            
            $calendarData[$date][] = [
                'count' => $row['total_actividades'],
                'status_name' => $row['nombre_estatus'],
                'class' => $colorClass
            ];
        }

        // Devolvemos array asociativo
        return ['calendarData' => $calendarData];
    }

    /**
     * Muestra la carga inicial del panel (cuando se entra por URL).
     */
    public function showSchedule() 
    {
        $data = $this->cargarDatosSchedule();
        $assets['styles'] = ['/css/Collaborator/schedule.css'];
        $assets['scripts'] = ['/js/Collaborator/schedule.js']; 

        $view_to_load = 'schedule.php';
        
        require __DIR__ . '/../../Views/Collaborator/Panel.php';
    }

    public function getActivitiesForDate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        // 1. Recibimos 'fecha' por POST (Coincide con tu JS)
        $date = $_POST['fecha'] ?? null;
        
        if (!$date) {
            echo json_encode(['error' => 'Fecha no proporcionada']);
            exit;
        }

        $userPublicId = null;
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 5) {
            $userPublicId = $_SESSION['user_id'];
        }

        $scheduleModel = new ScheduleModel();
        $activities = $scheduleModel->getActivitiesForDate($date, $userPublicId);

        echo json_encode($activities);
        exit;
    }
    
    public function showReports()
    {
        // Esta función puede usarse en el futuro si es necesario
    }

    public function createReport()
    {
        // Esta función puede usarse en el futuro si es necesario
    }

    /**
     * Carga el contenido de una sección dinámicamente (vía AJAX).
     * Sigue el patrón de AdminController::loadContent y ResidentController::loadContent.
     */
public function loadContent($view)
    {
        // 1. Verificador de Sesión (¡Esto está perfecto!)
        $sessionModel = new SessionDataModel(); 
        $verifier = new SessionVerifier($sessionModel);
        $keys_to_check = ['user_id', 'id_privada'];

        if (!$verifier->verify($keys_to_check)) {
            http_response_code(401); 
            echo json_encode([
                'success' => false, 
                'message' => 'Sesión inválida o expirada.',
                'errors' => $verifier->getErrors()
            ]);
            exit; 
        }

        // --- Lógica de vistas ---
        ob_start();
        $data = []; 
        $assets = ['styles' => [], 'scripts' => []];
        $view_file = basename($view, '.php');
        $view_path = __DIR__ . '/../../Views/Collaborator/sections/' . $view_file . '.php';

        if (!file_exists($view_path)) {
            ob_end_clean(); 
            http_response_code(404);
            $html_content = "<h2>Sección no encontrada</h2><p>La vista '{$view_file}.php' no existe.</p>";
            echo json_encode(['html' => $html_content, 'assets' => $assets]);
            return;
        }
        
        // 5. Cargar datos específicos para cada vista
        switch ($view_file) {
            case 'get-daily':
                // 1. Limpiamos el buffer para asegurar que solo salga JSON
                if (ob_get_length()) ob_clean();
                
                // 2. Obtenemos parámetros
                $date = $_GET['date'] ?? date('Y-m-d');
                $userPublicId = null;

                // 3. Validamos usuario (misma lógica que en cargarDatosSchedule)
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 5) { 
                    $userPublicId = $_SESSION['user_id'];
                }

                // 4. Consultamos el modelo
                $scheduleModel = new ScheduleModel();
                $activities = $scheduleModel->getActivitiesForDate($date, $userPublicId);

                // 5. Devolvemos JSON puro y terminamos la ejecución
                header('Content-Type: application/json');
                echo json_encode($activities);
                exit; // ¡Importante! Detiene la ejecución para no cargar vistas HTML
            // -----------------------------
            case 'schedule':
                
                // ----- ¡CORRECCIÓN 4! -----
                // Aquí cargamos los datos, igual que en showSchedule()
                $data = $this->cargarDatosSchedule();
                $assets['styles'][] = '/css/Collaborator/schedule.css';
                $assets['scripts'][] = '/js/Collaborator/schedule.js';
                break;

            case 'reports':
                // $assets['styles'][] = '/css/Collaborator/reports.css';
                break;
        }

        extract($data);
        require $view_path; 
        $html = ob_get_clean(); 

        header('Content-Type: application/json');
        echo json_encode(['html' => $html, 'assets' => $assets]);
    }
}