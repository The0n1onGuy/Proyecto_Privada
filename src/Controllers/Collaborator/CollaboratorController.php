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
        // Establecer la configuración regional a español para obtener el nombre del mes.
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252', 'es_ES', 'es');
        $now = new \DateTime();
        $monthName = ucfirst(strftime('%B', $now->getTimestamp()));
        $year = $now->format('Y');

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
            $date = $row['activity_date'];
            $status = strtolower($row['status_name']);
            
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
                'count' => $row['activity_count'],
                'status_name' => $row['status_name'],
                'class' => $colorClass
            ];
        }

        // Devolvemos array asociativo
        return [
            'calendarData' => $calendarData,
            'monthName' => $monthName,
            'year' => $year
        ];
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

    public function getScheduleForMonth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');

        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        $collaboratorPublicId = $_SESSION['user_id'] ?? null;

        if (!$collaboratorPublicId) {
            echo json_encode(['error' => 'Usuario no autenticado.']);
            exit;
        }

        // Establecer la configuración regional a español para obtener el nombre del mes.
        // Las cadenas pueden variar según el sistema operativo ('es_ES', 'esp').
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252', 'es_ES', 'es');
        $monthName = ucfirst(strftime('%B', mktime(0, 0, 0, $month, 1, $year)));

        $scheduleModel = new ScheduleModel();
        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate));
        
        $summary = $scheduleModel->getMonthlyActivitySummary($startDate, $endDate, $collaboratorPublicId);

        $calendarData = [];
        foreach ($summary as $item) {
            $date = $item['activity_date'];
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [];
            }

            $statusClass = '';
            switch (strtolower($item['status_name'])) {
                case 'completada': $statusClass = 'event-success'; break;
                case 'pendiente': $statusClass = 'event-warning'; break;
                case 'cancelada': $statusClass = 'event-danger'; break;
                default: $statusClass = 'event-info'; break;
            }

            $calendarData[$date][] = [
                'status_name' => htmlspecialchars($item['status_name']),
                'count' => (int) $item['activity_count'],
                'class' => $statusClass,
            ];
        }

        // Devolvemos un objeto JSON que incluye el mes y año para que el frontend pueda actualizar la vista.
        echo json_encode([
            'monthName' => $monthName,
            'year' => $year,
            'calendarData' => $calendarData
        ]);
        exit;
    }

    public function getActivitiesForDate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $date = $_GET['date'] ?? null;
        
        if (!$date) {
            echo json_encode(['error' => 'Parámetro "date" no proporcionado.']);
            exit;
        }

        // Se obtiene el ID público del usuario de la sesión, de forma segura.
        $userPublicId = $_SESSION['user_id'] ?? null;

        // Se añade una validación robusta. Si no hay ID de usuario, la sesión
        // probablemente ha expirado o el usuario no está autenticado.
        if (!$userPublicId) {
            echo json_encode(['error' => 'Usuario no autenticado o la sesión ha expirado.']);
            exit;
        }

        error_log("getActivitiesForDate - Recibido: date=$date, userPublicId=$userPublicId");

        try {
            $scheduleModel = new ScheduleModel();
            $activities = $scheduleModel->getActivitiesForDate($date, $userPublicId);

            error_log("getActivitiesForDate - Devolviendo " . count($activities) . " actividades");

            // Retornar un array vacío en lugar de null si no hay actividades
            echo json_encode($activities ?? []);
        } catch (Exception $e) {
            error_log("Error en getActivitiesForDate: " . $e->getMessage());
            echo json_encode(['error' => 'Error al cargar las actividades: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Crea un reporte para una actividad programada.
     */
    public function createActivityReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        // Obtener datos del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['error' => 'Datos vacíos en la petición']);
            exit;
        }

        // Validar datos requeridos
        $programada_id = $input['programada_id'] ?? null;
        $descripcion_ejecucion = $input['descripcion_ejecucion'] ?? null;
        $hubo_incidencia = isset($input['hubo_incidencia']) ? (int)$input['hubo_incidencia'] : 0;
        $descripcion_incidencia = $input['descripcion_incidencia'] ?? null;

        if (!$programada_id || !$descripcion_ejecucion) {
            echo json_encode(['error' => 'Faltan datos requeridos']);
            exit;
        }

        // Obtener ID del usuario de la sesión
        $userPublicId = $_SESSION['user_id'] ?? null;
        if (!$userPublicId) {
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit;
        }

        try {
            // Obtener ID numérico del usuario a partir del public_id
            $conn = \App\Core\Database::getConnection();
            $sql = "SELECT id_usuario FROM priv_usuarios WHERE public_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$userPublicId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            $usuario_id = $user['id_usuario'];

            // Verificar fecha de la actividad (no permitir reportes para fechas anteriores a hoy)
            $sqlCheck = "SELECT fecha_programada FROM priv_actividades_programadas WHERE programada_id = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->execute([$programada_id]);
            $activity = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if (!$activity) {
                echo json_encode(['error' => 'Actividad no encontrada']);
                exit;
            }

            $fechaProg = $activity['fecha_programada'];
            // Normalizar a fecha Y-m-d por si el valor contiene tiempo u otro formato
            $fechaProgDate = date('Y-m-d', strtotime($fechaProg));
            
            // Obtener la fecha de hoy DESDE LA BASE DE DATOS para evitar problemas de timezone del servidor
            $sqlDate = "SELECT DATE(NOW()) as today_date";
            $stmtDate = $conn->prepare($sqlDate);
            $stmtDate->execute();
            $dateResult = $stmtDate->fetch(\PDO::FETCH_ASSOC);
            $todayDate = $dateResult['today_date'];
            
            // Debug logging para entender la comparación
            error_log("createActivityReport DEBUG - fechaProgDate={$fechaProgDate}, todayDate (from DB)={$todayDate}, rawFechaProg={$fechaProg}, serverPHPTime=" . date('Y-m-d H:i:s'));
            
            // Solo rechazar si la actividad es anterior a hoy
            if ($fechaProgDate < $todayDate) {
                error_log("createActivityReport - intento de crear reporte en fecha pasada: programada_id={$programada_id}, fecha_programada={$fechaProg}, fechaProgDate={$fechaProgDate}, todayDate={$todayDate}");
                echo json_encode(['error' => 'No se pueden levantar reportes para actividades con fecha anterior a hoy.']);
                exit;
            }

            // Crear el reporte
            $scheduleModel = new ScheduleModel();
            $result = $scheduleModel->createActivityReport(
                $programada_id,
                $usuario_id,
                $descripcion_ejecucion,
                $hubo_incidencia,
                $descripcion_incidencia
            );

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                echo json_encode(['error' => $result['error']]);
            }
        } catch (Exception $e) {
            // Registrar el error interno pero retornar un mensaje genérico al cliente
            error_log("Error en createActivityReport: " . $e->getMessage());
            echo json_encode(['error' => 'Ocurrió un error al procesar la solicitud. Por favor intente más tarde.']);
        }
        exit;
    }
    
    public function showReports()
    {
           $assets['styles'] = ['/css/Collaborator/schedule.css', '/css/Collaborator/reports.css'];
        $assets['scripts'] = [];

        $view_to_load = 'reports.php';
        
        require __DIR__ . '/../../Views/Collaborator/Panel.php';
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
            // CORRECCIÓN 3: Se elimina el caso 'get-daily' de aquí.
            // La obtención de datos para el modal se debe hacer llamando directamente
            // al endpoint getActivitiesForDate para mantener el código limpio y ordenado.
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