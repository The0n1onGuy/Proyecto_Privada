<?php

namespace App\Controllers\Collaborator;

use App\Models\SessionDataModel;
use App\Core\SessionVerifier;
use App\Models\Collaborator\ScheduleModel;
use Exception;

class CollaboratorController
{

/**
     * Carga los datos necesarios para la vista 'schedule'.
     * Esta función privada evita duplicar código entre showSchedule y loadContent.
     *
     * @return array Los datos para la vista (ej. ['calendarData' => [...] ]).
     */
    private function cargarDatosSchedule(): array
    {
        // 1. Obtener el mes actual
        $fecha_inicio = date('Y-m-01');
        $fecha_fin = date('Y-m-t');
        
        // 2. Aplicar filtro si el usuario es un Colaborador (Rol 5)
        $id_filtro_usuario = null;
            
        // ----- ¡CORRECCIÓN 1! -----
        // Tu sesión usa 'user_role' y el valor es un ID numérico (ej. 5), no un string.
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 5) { 
            $id_filtro_usuario = $_SESSION['user_id'];
        }

        // 3. Obtener el resumen de actividades
        $scheduleModel = new ScheduleModel();
        $summaryData = $scheduleModel->getMonthlyActivitySummary($fecha_inicio, $fecha_fin, $id_filtro_usuario);

        // 4. Procesar los datos para el Front-End
        $calendarData = [];
        foreach ($summaryData as $row) {
            $fecha = $row['fecha_programada'];
            // Usamos 'nombre_estatus' tal como lo definiste en el Modelo
            $estatus = $row['nombre_estatus']; 
            $calendarData[$fecha][$estatus] = [
                'count' => $row['total_actividades'],
                // (Opcional) Puedes asignar colores fijos aquí si el modelo no los trae
                // 'color' => $this->getColorForStatus($estatus) 
            ];
        }

        // 5. Devolver los datos listos para 'extract()'
        return ['calendarData' => $calendarData];
    }

    /**
     * Muestra la carga inicial del panel (cuando se entra por URL).
     */
    public function showSchedule() 
    {
        // 1. Cargar los datos del calendario
        $data = $this->cargarDatosSchedule();
        
        // 2. Definir los assets para esta vista
        
        // ----- ¡CORRECCIÓN 2! -----
        // La ruta de tu JS era 'Resident' en lugar de 'Collaborator'
        $assets['styles'] = ['/css/Collaborator/schedule.css'];
        $assets['scripts'] = ['/js/Collaborator/schedule.js']; 

        // 3. Definir la vista a cargar dentro del panel
        $view_to_load = 'schedule.php';
        
        // 4. Cargar la plantilla principal (Panel.php)
        // Panel.php usará $data, $assets y $view_to_load
        require __DIR__ . '/../../Views/Collaborator/Panel.php';
    }

    /**
     * Maneja la llamada AJAX POST para obtener actividades de un día específico.
     */
    public function getActivitiesForDate() 
    {
        // 1. Obtener la fecha del request (ej. $_POST['fecha'])
        $fecha_seleccionada = $_POST['fecha'] ?? null; 
        
        if (!$fecha_seleccionada) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'No se proporcionó la fecha.']);
            exit;
        }

        // 2. Aplicar filtro
        $id_filtro_usuario = null;
        
        // ----- ¡CORRECCIÓN 3! -----
        // Corregir la clave de sesión ('user_role') y el valor (5)
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 5) {
            $id_filtro_usuario = $_SESSION['user_id'];
        }

        // 3. Obtener los detalles
        $scheduleModel = new ScheduleModel();
        $details = $scheduleModel->getDetailedActivitiesForDate($fecha_seleccionada, $id_filtro_usuario);

        // 4. Devolver los datos como JSON
        header('Content-Type: application/json');
        echo json_encode($details);
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