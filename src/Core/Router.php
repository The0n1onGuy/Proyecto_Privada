<?php

namespace App\Core;

use App\Controllers\LoginController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Resident\ResidentController;

class Router
{
    public function handleRequest()
    {
        // Limpiamos la URL para que no afecten los parámetros GET (ej. ?v=123)
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        $isLoggedIn = isset($_SESSION['user_id']);
        $userRole = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : null;

        // 1. Redirección de usuarios ya logueados que visitan la raíz
        if ($url === '/' && $isLoggedIn) {
            switch ($userRole) {
                case 1: header('Location: /admin'); exit;
                case 2: header('Location: /resident'); exit;
            }
        }

        // 2. Manejo del formulario de login
        if ($url === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new LoginController();
            $role = $controller->processLogin();
            if ($role) {
                switch ($role) {
                    case 1: header('Location: /admin'); break;
                    case 2: header('Location: /resident'); break;
                    default: header('Location: /'); break;
                }
            } else {
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

        // --- RUTAS DE ADMINISTRADOR ---
        if ($isLoggedIn && $userRole === 1 && strpos($url, '/admin') === 0) {
            $controller = new AdminController();

            // A. RUTA ESPECIAL PARA JAVASCRIPT (Carga de contenido dinámico)
            if (preg_match('/^\/admin\/content\/(\w+)$/', $url, $matches)) {
                $sectionName = $matches[1]; // Captura 'dashboard' de la URL
                $controller->loadContent($sectionName);
                return; // Detenemos aquí para no cargar el layout completo
            }
            
            // B. RUTAS PARA NAVEGACIÓN DIRECTA (Carga de página completa)
            switch ($url) {
                case '/admin':
                case '/admin/dashboard':
                    $controller->showDashboard();
                    break;

                case '/admin/users': // Aquí irían tus otras secciones en el futuro
                    $controller->showUsers();
                    break;
                    
                case '/admin/privada': 
                    $controller->showPrivadas();
                    break;

                case '/admin/servicios': 
                    $controller->showServicios();
                    break;
                    
                case '/admin/colaboradores': 
                    $controller->showColaboradores();
                    break;
                    //Funcion de actualizacion para colaboradores actua cuando se hace una solictud y hay un cambio de url
                case '/admin/colaboradores/update':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->updateColaborador();
                    }
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

        // --- OTRAS RUTAS (LOGIN, RESIDENTE, 404) ---
        if ($url === '/' && !$isLoggedIn) {
            $controller = new LoginController();
            $controller->showLogin();
        } elseif ($url === '/resident' && $isLoggedIn && $userRole === 2) {
            $controller = new ResidentController();
            $controller->showDashboard();
        } else {
            // Si llega hasta aquí, significa que la URL no coincide con ninguna regla
            if ($isLoggedIn) {
                // Si el usuario está logueado pero en una URL incorrecta, lo mandamos a la raíz
                header('Location: /');
            } else {
                // Si no está logueado y la URL no es el login, es un error 404
                http_response_code(404);
                echo "Error de enrutamiento. Página no encontrada.";
            }
            exit;
        }
    }
}