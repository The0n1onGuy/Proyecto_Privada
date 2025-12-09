<?php

namespace App\Core;

// CLASES CONTROLADORES Y MODELO DE UTILIDAD
use App\Controllers\LoginController;
use App\Controllers\Admin\AdminController;
use App\Models\Admin\UtilityModel;
use App\Controllers\Resident\ResidentController;
use App\Controllers\Collaborator\CollaboratorController;

// CLASES DE VERIFICACIÓN
use App\Models\SessionDataModel;
use App\Core\SessionVerifier;


class Router
{
    public function handleRequest()
    {
        // Limpiamos la URL para que no afecten los parámetros GET (ej. ?v=123)
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        // $isLoggedIn = isset($_SESSION['user_id']);
        $isLoggedIn = isset($_SESSION['user_id']);
        $userRole = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : null;

        // Redirección de usuarios ya logueados que visitan la raíz
        if ($url === '/' && $isLoggedIn) {
            switch ($userRole) {
                case 1: header('Location: /admin/select-private'); exit;
                case 2: header('Location: /resident'); exit;
                case 5: header('Location: /collaborator'); exit;
            }
        }

        if ($url === '/admin/' && $isLoggedIn) {
            switch ($userRole) {
                case 1: header('Location: /admin/select-private'); exit;
            }
        }

        if ($url === '/admin' && $isLoggedIn) {
            switch ($userRole) {
                case 1: header('Location: /admin/select-private'); exit;
            }
        }

        // 2. Manejo del formulario de login y cuando se solicita un metodo POST
        if ($url === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new LoginController();
            $role = $controller->processLogin();
            if ($role) {
                switch ($role) {
                    //Redireccionalo deacuerdo a su rol 1 Admin, 2 Residente
                    case 1: header('Location: /admin/select-private'); break;
                    case 2: header('Location: /resident'); break;
                    case 5: header('Location: /collaborator'); exit;
                    default: header('Location: /'); break;
                }
            } else {
                //Si la persona no ha iniciado sesion previamente y no va a "precisamente" a
                //vista del login rebotalo y reenvialo al login
                header('Location: /');
            }
            exit;
        }
        
        // 3. Manejo del cierre de sesión
        elseif ($url === '/logout' && $isLoggedIn) {
            session_destroy();
            header('Location: /');
            exit;
        }

        //Pregunta si el rol es admin, ya inicio sesion y busca el enlace "admin"
        if ($isLoggedIn && $userRole === 1 && strpos($url, '/admin') === 0) {
            
            // Verificamos la sesión antes de hacer cualquier otra cosa.
            $keysToVerify = [];
            if ($url === '/admin/select-private' || $url === '/admin/set-private') {
                // Estas rutas solo necesitan que el usuario exista (aún no ha elegido privada)
                // $keysToVerify = ['user_id'];
                $keysToVerify = ['user_id'];
            } else {
                // Todas las demás rutas de admin (dashboard, API, POSTs) necesitan la privada
                // $keysToVerify = ['user_id', 'public_id_privada'];
                $keysToVerify = ['user_id', 'id_privada'];
            }
        
            if (!$this->runVerification($keysToVerify)) {
                // El verificador falló (sesión corrupta, ID no existe, etc.)
                
                session_destroy();
                header('Location: /'); // Botar al login
                exit;
            }

            $controller = new AdminController();            
            $method = $_SERVER['REQUEST_METHOD'];
            //LLAMAS DE API 
            //Obtener los datos de residentes
            if (preg_match('/^\/admin\/api\/resident\/([\w-]+)$/', $url, $matches)) {
                $controller->getResidentData($matches[1]);
                return;
            }//Obtener los datos de residentes
            if (preg_match('/^\/admin\/api\/collaborator\/([\w-]+)$/', $url, $matches)) {
                $controller->obtenDatosdelColaborador($matches[1]);
                return;
            }
            //Mostrar un modal dentro de configuracion
            if (preg_match('/^\/admin\/config-modal\/([\w-]+)$/', $url, $matches)) {
                $modalName = $matches[1]; // Captura 'edit-profile'
                $controller->procesa_Modal_Configuracion($modalName);
                return; // Detenemos aquí para no cargar el layout
            }
            //Mostrar un modal de los detalles del servicio
            if (preg_match('/^\/admin\/api\/servicio\/([\w-]+)$/', $url, $matches)) {
                $controller->obtenServicioData($matches[1]);
                return;
            }

            // --- MANEJO DE PETICIONES POST
            if ($method === 'POST') {
                switch ($url) { //FUNCIONES DE OPERACIONES CRUD
                    //FUNCIONES DE RESIDENTES
                    case '/admin/residentes/create':
                        $controller->operacion_Residentes(1);
                        return; 
                    case '/admin/residentes/update':
                        $controller->operacion_Residentes(2);
                        return; 
                    case '/admin/residentes/delete':
                        $controller->operacion_Residentes(3);
                        return; 
                    case '/admin/residentes/createEX':
                        $controller->operacion_Residentes(4);
                        return; 

                    //FUNCIONES DE COLABORADORES
                    case '/admin/colaboradores/create':
                        $controller->operacion_Colaborador(1);
                        return; 
                    case '/admin/colaboradores/update':
                        $controller->operacion_Colaborador(2);
                        return; 
                    case '/admin/colaboradores/delete':
                        $controller->operacion_Colaborador(3);
                        return;     
                    //FUNCIONES DE SERVICIOS
                    case '/admin/servicios/create':
                        $controller->operacion_Servicios(1);
                        return; 
                    case '/admin/servicios/update':
                        $controller->operacion_Servicios(2);
                        return; 
                    case '/admin/servicios/delete':
                        $controller->operacion_Servicios(3);
                        return;
                    //FUNCIONES DE SERVICIOS
                    case '/admin/proveedores/create':
                        $controller->operacion_Servicios(1);
                        return; 
                    //FUNCIONES DE AVISOS
                    case '/admin/avisos/create':
                        // $controller->Procesa_Crear_Colaborador();
                        $controller->operacion_Avisos(1);
                        return;                    
                    if ($url === '/admin/avisos/delete') {
                        $controller->operacion_Avisos(2);
                        return;
                    }
                    //FUNCIONES DE VISITAS
                    if ($url === '/resident/visitas/create') {
                    $controller->createVisita();
                    return;
                    }
                    if ($url === '/resident/visitas/update-status') {
                        $controller->updateVisitaStatus();
                        return;
                    }

                }
            }
            // A. RUTA ESPECIAL PARA JAVASCRIPT (Carga de contenido dinámico)
            if (preg_match('/^\/admin\/content\/(\w+)$/', $url, $matches)) {
                $sectionName = $matches[1]; // Captura 'dashboard' de la URL
                $controller->loadContent($sectionName);
                return; // Detenemos aquí para no cargar el layout completo
            }
            
            // B. RUTAS PARA NAVEGACIÓN DIRECTA (Carga de página completa)
            switch ($url) {
                case '/admin/select-private':
                    $controller->showPrivateSelection();
                    break;

                case '/admin/set-private':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {                        
                        $public_id = $_POST['id_privada_publica']; 
                        // $public_iduser = $_POST['public_id_usuario']; 

                        // Simplemente validamos que no esté vacío (o que sea un UUID válido)

                        if (empty($public_id)) {
                            session_destroy();
                            header('Location: /');
                            exit;
                        }

                        // Guardamos el UUID string en la sesión.
                        // $_SESSION['public_id_privada'] = $public_id;
                        $_SESSION['id_privada'] = $public_id;


                        header('Location: /admin/dashboard');
                        exit;
                    }
                    break;
                case '/admin':
                    case '/admin/privadas/list':
                        require_once __DIR__ . '/../Models/Admin/UtilityModel.php';
                        $UtilityModel = new UtilityModel();
                        $privadas = $UtilityModel->obtenTodosPrivadas();

                        header('Content-Type: application/json');
                        echo json_encode($privadas);
                        break;
                case '/admin/dashboard':
                    $controller->showDashboard();
                    break;

                case '/admin/residents': 
                    $controller->showResidents();
                    break;
                    
                case '/admin/servicios': 
                    $controller->showServicios();
                    break;
                    
                case '/admin/colaboradores': 
                    $controller->showColaboradores();
                    break;
                
                case '/admin/avisos': 
                    $controller->showavisos();
                    break;
                case '/admin/visitas': 
                    $controller->showavisos();
                    break;
                case '/admin/configs': 
                    $controller->showConfigs();
                    break;
                // case '/admin/sección': // Aquí irían tus otras secciones en el futuro
                //     $controller->showFunction();
                //     break;

                default:
                    // Si la URL no coincide, lo mandamos al dashboard para evitar errores
                    header('Location: /admin/dashboard');
                    exit;
            }
            return; // Detenemos aquí para no procesar más rutas
        }
        if ($isLoggedIn && $userRole === 2 && strpos($url, '/resident') === 0) {
            
            // --- NUEVO INICIO ---
            // PUNTO DE CONTROL DE RESIDENTE
            
            // Movemos la lógica que busca 'id_info' aquí.
            // Esto asegura que 'id_info' esté presente ANTES de la verificación.
            if (!isset($_SESSION['id_info'])) {
                $startModel = new \App\Models\Resident\StartModel();
                $userInfo = $startModel->getResidentInfo($_SESSION['user_id']);
                $_SESSION['id_info'] = $userInfo['id_info'] ?? null;
            }

            // Ahora verificamos las claves esenciales del residente
            $keysToVerify = ['user_id', 'id_info'];
        
            if (!$this->runVerification($keysToVerify)) {
                // Si AÚN falla (user_id malo, id_info nulo o no encontrado)
                session_destroy();
                header('Location: /'); // Botar al login
                exit;
            }
            // --- NUEVO FIN ---


            $controller = new ResidentController();

            // --- MANEJO DE PETICIONES POST ---
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                //Rutas para avisos
                if ($url === '/resident/avisos/create') {
                    $controller->createAviso();
                    return;
                }
                if ($url === '/resident/avisos/delete') {
                    $controller->deleteAviso();
                    return;
                }
                if ($url === '/resident/visitas/create') {
                    $controller->createVisita();
                    return;
                }
                if ($url === '/resident/visitas/update-status') {
                    $controller->updateVisitaStatus();
                    return;
                }
            }
            // A. CARGA DE CONTENIDO MEDIANTE EL SIDEBAR
            if (preg_match('/^\/resident\/content\/(\w+)$/', $url, $matches)) {
                $sectionName = $matches[1]; // Captura 'dashboard' de la URL
                $controller->loadContent($sectionName);
                return; // Detenemos aquí para no cargar el layout completo
            }
            
            // B. RUTAS PARA NAVEGACIÓN DIRECTA (Carga de página completa)
            switch ($url) {
                case '/resident':
                case '/resident/start':
                    $controller->showStart();
                    break;
                case '/resident/avisos':
                    $controller->showAvisos();
                    break;
                case '/resident/residents':
                    $controller->showResidents();
                    break;
                case '/resident/visitas':
                    $controller->showVisitas();
                    break;
                    
                // case '/admin/sección': // Aquí irían tus otras secciones en el futuro
                //     $controller->showFunction();
                //     break;

                default:
                    // Si la URL no coincide, lo mandamos al dashboard para evitar errores
                    header('Location: /resident/start');
                    exit;
            }
            return; // Detenemos aquí para no procesar más rutas
        }
        // --- OTRAS RUTAS (LOGIN, 404, Y REDIRECCIÓN DE INVITADOS) ---
        if ($isLoggedIn && $userRole === 5 && strpos($url, '/collaborator') === 0) {
            
            $keysToVerify = ['user_id', 'id_privada'];
        
            if (!$this->runVerification($keysToVerify)) {
                // El verificador falló (sesión corrupta, ID no existe, etc.)
                session_destroy();
                header('Location: /'); // Botar al login
                exit;
            }
             // 4. PUNTO DE CONTROL DE COLABORADOR

            $controller = new CollaboratorController();

            // --- MANEJO DE PETICIONES POST ---
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($url === '/collaborator/getActivitiesForDate') {
                    $controller->getActivitiesForDate();
                    return;
                }
            }
            // A. CARGA DE CONTENIDO MEDIANTE EL SIDEBAR
            if (preg_match('/^\/collaborator\/content\/(\w+)$/', $url, $matches)) {
                $sectionName = $matches[1]; // Captura 'dashboard' de la URL
                $controller->loadContent($sectionName);
                return; // Detenemos aquí para no cargar el layout completo
            }
            
            // B. RUTAS PARA NAVEGACIÓN DIRECTA (Carga de página completa)
            switch ($url) {
                case '/collaborator':
                case '/collaborator/schedule':
                    $controller->showSchedule();
                    break;
                case '/collaborator/reports':
                    $controller->showReports();
                    break;

                default:
                    // Si la URL no coincide, lo mandamos al dashboard para evitar errores
                    header('Location: /collaborator/schedule');
                    exit;
            }
            return; // Detenemos aquí para no procesar más rutas
        }
        // Si el usuario NO está logueado
        if (!$isLoggedIn) {
            
            // Y está en la raíz, muestra el login (única página permitida)
            if ($url === '/') {
                $controller = new LoginController();
                $controller->showLogin();
                exit; // ¡Importante salir aquí!
            }
            
            // Y está en CUALQUIER OTRA PÁGINA (ej. /resident/start)
            // lo redirigimos a la raíz (login).
            // (El /login POST ya fue manejado por un 'if' anterior)
            else {
                 header('Location: /');
                 exit;
            }
        } 
        
        // 2. Si el usuario SÍ está logueado (else)
        else {
            // Si llega hasta aquí, significa que:
            // 1. $isLoggedIn = true
            // 2. La URL NO es / (manejado al inicio)
            // 3. La URL NO es /admin/* (manejado en el bloque admin)
            // 4. La URL NO es /resident/* (manejado en el bloque resident)
            // Por lo tanto, es un verdadero 404 o una URL perdida.
            
            // Lo mandamos a la raíz, donde la lógica inicial lo
            // redirigirá a su panel correspondiente.
            header('Location: /');
            exit;
        }
    }

    // --- NUEVO INICIO ---
    /**
     * MÉTODO HELPER PARA EJECUTAR LA VERIFICACIÓN
     * Ejecuta el SessionVerifier con las claves proporcionadas.
     *
     * @param array $keysToVerify Claves de sesión a verificar.
     * @return bool True si la verificación es exitosa, False si falla.
     */
    private function runVerification(array $keysToVerify): bool
    {
        $model = new SessionDataModel();
        $verifier = new SessionVerifier($model);
        
        // El verifier devolverá false si alguna clave falla
        // (no existe, está vacía o no es coherente con la BD)
        return $verifier->verify($keysToVerify);
    }

}