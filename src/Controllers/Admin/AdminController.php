<?php

namespace App\Controllers\Admin;



use App\Models\Admin\AvisosModel;
use App\Models\Admin\ColaboradoresModel;
use App\Models\Admin\DashboardModel;
use App\Models\Admin\ResidentsModel;
use App\Models\Admin\ProveedorModel;
use App\Core\SessionVerifier;
use App\Models\SessionDataModel;
use App\Models\Admin\ServiciosModel;
use App\Models\Admin\UtilityModel;

// use App\Models\Admin\ServiciosModel;
// use App\Models\Admin\AvisosModel;
// use App\Models\Admin\UtilityModel;
// use App\Models\Admin\DashboardModel;
// use App\Models\Admin\ResidentsModel;
// use App\Models\Admin\ProveedorModel;
// use App\Models\Admin\ColaboradoresModel;
// use App\Core\SessionVerifier;
// use App\Models\SessionDataModel;
use Exception;

class AdminController
{
    public function showPrivateSelection()
    {
        $utilityModel = new UtilityModel();
        $data['privadas'] = $utilityModel->obtenTodosPrivadas();
        extract($data);
        require __DIR__ . '/../../Views/Admin/SelectPrivate.php';
    }
    private function cargarDatosDelPanel(): array
    {
        if (!isset($_SESSION['id_privada'])) {
            header(header: 'Location: /admin/select-private');
            exit;
        }

        $utilityModel = new UtilityModel();
        $data['todas_las_privadas'] = $utilityModel->obtenTodosPrivadas();
        
        // Leemos el PUBLIC_ID (UUID) de la sesión
        $public_id_actual = $_SESSION['id_privada'] ?? null; 
        $data['privada_actual'] = null;
        if (!empty($data['todas_las_privadas']) && $public_id_actual) {
            foreach ($data['todas_las_privadas'] as $privada) {
                // Comparamos el PUBLIC_ID de la DB con el PUBLIC_ID de la sesión

                if ($privada['public_id'] == $public_id_actual) { 
                    $data['privada_actual'] = $privada;
                    break;
                }
            }
        }
        return $data;
    }
    public function showDashboard()
    {
        //Verifica si hay un id 
        $data = $this->cargarDatosDelPanel();
        //Llama en un objecto la clase y funciones
        $dashboardModel = new DashboardModel();
        $privadaModel = new UtilityModel();
        // $paymentStats = $dashboardModel->obtenPagosStat();
        // $reportes = $dashboardModel->getReportes();
        $avisos = $dashboardModel->getAvisosD($_SESSION['id_privada']);
        // $data['reportes'] = $reportes;
        $data['avisos'] = $avisos;
        $data['paymentStatsJSON'] = json_encode($dashboardModel->obtenPagosStat($_SESSION['id_privada']));
        //Llama los elementos de la vista (estilo y scripts; Codigo en javascript para logica de vista)

        $assets['styles'] = ['/css/Admin/admin_dashboard.css'];                
        $assets['scripts'] = ['https://cdn.jsdelivr.net/npm/chart.js'];
        // $assets['scripts'] = ['https://cdn.jsdelivr.net/npm/chart.js' , '/js/Admin/admin_dashboard.js'];
        $view_to_load = 'dashboard.php';
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function showResidents()
    {
        $data = $this->cargarDatosDelPanel();
        //Obten el filtro y busca si son "owners" (Propietarios en base de datos)
        $filter = $_GET['filter'] ?? 'owners';
        $residentsModel = new ResidentsModel();
        $utilityModel = new UtilityModel();
        //Obten todos los residentes con la id de la sesion y dale un filtro 
        $residents = $residentsModel->getAllResidents($_SESSION['id_privada'], $filter);
        //Almacena en un JSON los datos y el filtro definido
        $data['residents'] = $residents;
        $data['Presidentes'] = $utilityModel->obtenTodosPrivadas();
        $data['residentesEstados'] = $utilityModel->obtenPrimDatosEstatus();
        $data['currentFilter'] = $filter;
        //Llama los elementos de la vista (estilo y scripts; Codigo en javascript para logica de vista)
        $assets['styles'] = ['/css/Utilities/DataTables.css', '/css/Admin/admin_residents.css'];
        $assets['scripts'] = ['/js/admin_residentes.js'];
        $view_to_load = 'residents.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    
    public function getResidentData($id)
    {
        // Obtiene los datos de un residente específico por ID para la API.
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
    public function operacion_Residentes($caso){
        $casoS = $caso;
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        switch ($casoS){
            case 1: // CREAR UN RESIDENTE Y VERIFICAR USUARIO / NÚMERO DE CASA
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                try {
                    // Instancia del modelo de privadas
                    $PrivadasModel = new UtilityModel();

                    // Verificación si solo se quiere checar existencia del username ---
                    if (!empty($data['verificar']) && $data['verificar'] === true && !empty($data['username'])) {
                        $username = trim($data['username']);
                        $existe = $PrivadasModel->verificarUsuario($username);
                        echo json_encode(['existe' => $existe]);
                        exit;
                    }

                    // Verificación si solo se quiere checar existencia del número de casa ---
                    if (!empty($data['verificar_casa']) && $data['verificar_casa'] === true && !empty($data['num_casa'])) {
                        $numCasa = trim($data['num_casa']);
                        $existe = $PrivadasModel->verificarNumCasa($numCasa);
                        echo json_encode(['existe' => $existe]);
                        exit;
                    }

                    // Validación básica de datos requeridos para crear residente ---
                    if (empty($data['username']) || empty($data['num_casa']) || empty($data['nombres'])) {
                        throw new Exception('Faltan datos requeridos.');
                    }

                    $username = trim($data['username']);
                    $numCasa = trim($data['num_casa']);

                    // Verificación de duplicidad antes de crear ---
                    if ($PrivadasModel->verificarUsuario($username)) {
                        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado.']);
                        exit;
                    }

                    if ($PrivadasModel->verificarNumCasa($numCasa)) {
                        echo json_encode(['success' => false, 'message' => 'El número de casa ya tiene un propietario.']);
                        exit;
                    }
                    $data['public_id_privada'] = $_SESSION['id_privada']; // Recuerda que $_SESSION['id_privada'] es el UUID
                    // Crear residente ---
                    $residentsModel = new ResidentsModel(); 
                    $success = $residentsModel->creaResidente($data);

                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Residente creado exitosamente.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al crear el residente.']);
                    }

                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }

                exit;
            case 2: // ACTUALIZAR UN RESIDENTE
                $data = $_POST;
                if (isset($data['id_info'])) { 
                    $data['public_id_info'] = $data['id_info']; 
                }
                $residentsModel = new ResidentsModel();
                $result = $residentsModel->actualizaResident($data);

                // Enviamos el resultado directamente al JS
                echo json_encode($result);
                // $success = $residentsModel->actualizaResident($data);
                // if ($success) {
                //     echo json_encode(['success' => true, 'message' => 'Residente act correctamente.']);
                // } else {
                //     echo json_encode(['success' => false, 'message' => 'Error al actualizar el residente.']);
                // }
                exit;
                
            case 3: // ELIMINAR UN RESIDENTE
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                try {
                    $public_id_info = $data['public_id_info'] ?? $data['id_info'] ?? null;
                    if (empty($public_id_info)) {
                        throw new Exception('No se proporcionó el ID del residente.');
                    }

                    $residentsModel = new ResidentsModel(); 
                    $success = $residentsModel->eliminaResidente($public_id_info);
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
                    if (empty($data['num_casa'])) {
                        throw new Exception('Falta el número de casa.');
                    }

                    $privadasModel = new UtilityModel();
                    $propietario = $privadasModel->buscarPropietarioPorCasa($data['num_casa'] ?? '');
                    $nombrePropietario = $propietario 
                        ? $propietario['nombres'] . ' ' . $propietario['apellido_p'] . ' ' . $propietario['apellido_m'] 
                        : '';

                    if (!empty($data['soloPropietario'])) {
                        echo json_encode([
                            'success' => true,
                            'propietario' => $nombrePropietario
                        ]);
                        exit;
                    }
                    
                    // Validación básica de datos
                    $data['id_privada'] = $_SESSION['id_privada'] ?? 0; // <-- ADD THIS LINE
                    $data['public_id_privada'] = $_SESSION['id_privada'];
                    if (empty($data['nombres']) || empty($data['num_casa']) || empty($data['id_privada'])) {
                        throw new Exception('Faltan datos requeridos (nombre, casa o privada).');
                    }
                    // Llama a la nueva función en el modelo
                    $residentsModel = new ResidentsModel(); 
                    $success = $residentsModel->creaResidenteExtra($data);
                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Colaborador creado exitosamente.']);
                    } else {
                        // Este caso es raro si se usa try/catch, pero es una salvaguarda.
                        echo json_encode(['success' => false, 'message' => 'Error al crear el residente.']);
                    }
                    } catch (Exception $e) {
                    // Si el modelo lanza una excepción, la atrapamos aquí.
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
                }
                exit;
                    }
    }
    
    public function showServicios()
    {
        $serviciosModel = new ServiciosModel(); // DECLARO EL MODELO Y MODELO DE UTILIDAD ANTES PARA NO REPETIR EN CADA CASO Y OPTIMIZAR
        $utilityModel = new UtilityModel();
        // $servicios = $serviciosModel->obtenServicios($_SESSION['id_privada']);
        //Almacena en un JSON los datos y el filtro definido
        // $data['servicios'] = $servicios;
        $data['lista_proveedores'] = $serviciosModel->obtenProveedoresPorPrivada($_SESSION['id_privada']);
        $data['lista_servicios'] = $serviciosModel->obtenServiciosAsignados($_SESSION['id_privada']);
        // $data['Priv_servicios'] = $utilityModel->obtenTodosPrivadas();
        // $data['estatus_servicios'] = $utilityModel->obtenPrimDatosEstatus();       
        $assets['styles'] = ['/css/Admin/admin_servicios.css'];
        $assets['scripts'] = ['/js/Admin/admin_servicios.js' , '/js/Admin/admin_proveedor.js'];

        $view_to_load = 'servicios.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function obtenServicioData($public_id)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $serviciosModel = new ServiciosModel();
        $data = $serviciosModel->obtenServicioDetalles($public_id);

        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado.']);
        }
        exit;
    }
    public function operacion_Servicios($caso){
        //REMPLAZA TODO CON RESPECTO A SERVICIOS ACTUALMENTE
        $casoS = $caso;
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        $servicioModel = new ServiciosModel(); // DECLARO EL MODELO Y MODELO DE UTILIDAD ANTES PARA NO REPETIR EN CADA CASO Y MEJOR OPTIMIZAR
        $proveedorModel = new ProveedorModel();
        $utilityModel = new UtilityModel();

        switch ($casoS){
            case 1: // CREANDO SERVICIOS
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            try {
                // Antes de crear, también se puede verificar de nuevo (opcional)
                $success = $proveedorModel->crearProveedor($data);

                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Colaborador creado exitosamente.']);
                } else {
                    throw new Exception('No se pudo crear proveedor.');
                }

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
            }
            exit;

            case 2: // AQUI COLOCA TU LOGICA PARA ACTUALIZAR SERVICIOS
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
                
            case 3: // AQUI COLOCA TU LOGICA PARA ELIMINAR SERVICIOS
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                try {
                    if (empty($data['public_id_usuario'])) {
                        throw new Exception('No se proporcionó el ID del colaborador a eliminar.');
                    }
                
                    $success = $servicioModel->eliminarColaborador($data['public_id_usuario']);

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
            case 4: // Añadir proveedor sin servicio
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                try {
                    if (empty($data['public_id_usuario'])) {
                        throw new Exception('No se proporcionó el ID del colaborador a eliminar.');
                    }
                
                    $success = $proveedorModel->crearProveedor($data);

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
         public function showColaboradores()
    {
        $data = $this->cargarDatosDelPanel();
        //Llama en un objecto la clase y funciones
        $colaboradorModel = new ColaboradoresModel();
        $utilityModel = new UtilityModel();

        $data['roles'] = $utilityModel->obtenDatosColabRoles();
        //Haz una consulta de los colaboradores con los roles y la privada escojida
        $colaboradores = $colaboradorModel->obtenColaboradores($_SESSION['id_privada'], $data['roles']);
        $data['colaboradores'] = $colaboradores;
        $data['Pcolaboradores'] = $utilityModel->obtenTodosPrivadas();
        $data['colabestatus'] = $utilityModel->obtenPrimDatosEstatus();
        
        //Llama los elementos de la vista (estilo y scripts; Codigo en javascript para logica de vista)
        $assets['styles'][] = '/css/Admin/admin_colaboradores.css';
        $assets['scripts'] = ['/js/Admin/admin_colaboradoresAnadir.js' , '/js/Admin/admin_colaboradores.js'];

        $view_to_load = 'colaboradores.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function obtenDatosdelColaborador($public_id)
    {
        // API para obtener datos de un colaborador específico
        header('Content-Type: application/json');
        
        // Validación básica de sesión (ya hecha por el router, pero doble check no sobra)
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $colaboradorModel = new ColaboradoresModel();
        $data = $colaboradorModel->obtenColaboradorporID($public_id);

        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Colaborador no encontrado.']);
        }
        exit;
    }

    public function operacion_Colaborador($caso){
        $casoS = $caso;
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        switch ($casoS){
            case 1: // CREAR O VERIFICAR UN COLABORADOR
            // Se obtiene el contenido JSON enviado desde el cliente (por ejemplo, desde fetch() en JS)
            $json = file_get_contents('php://input');
            // Se decodifica el JSON en un arreglo asociativo para poder manipular los datos
            $data = json_decode($json, true);

            try {
                // ------------------------------------------------------------
                // Bloque de verificación de usuario duplicado sin crear el registro
                // ------------------------------------------------------------
                // Instancia del modelo de utilidad para verificar usuarios
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
                // (Doble verificación por si acaso)
                if ($utilityModel->verificarUsuario($data['username'])) {
                    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado.']);
                    exit;
                }
                // ------------------------------------------------------------
                // Bloque de creación real del colaborador
                // ------------------------------------------------------------
                if (empty($data['username']) || empty($data['password']) || empty($data['nombres'])) {
                    throw new Exception('Faltan datos requeridos.');
                }
                // Antes de crear, también se puede verificar de nuevo (opcional)
                
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
                try {
                    //Despues almacena en un JSON el id_usuario y nombres, revisa si los campos estan vacios y envia un mensaje
                    $input = json_decode(file_get_contents('php://input'), true);

                    if (empty($input['id_usuario']) || empty($input['nombres'])) {
                        throw new Exception('Faltan datos requeridos.');
                    }
                    //Declara un objecto con el modelo y llama la actualizacion
                    $colaboradorModel = new ColaboradoresModel();            
                    $success = $colaboradorModel->actualizaColaborador($input);

                    //Envia una respuesta de acuerdo al estado por medio de un JSON
                    
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
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                try {
                    if (empty($data['public_id_usuario'])) {
                        throw new Exception('No se proporcionó el ID del colaborador a eliminar.');
                    }
                    
                    $colaboradorModel = new ColaboradoresModel();
                    $success = $colaboradorModel->eliminarColaborador($data['public_id_usuario']);

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
        $avisosModel = new AvisosModel();
        $utilityModel = new UtilityModel();
        $data = $this->cargarDatosDelPanel();
        $data['avisos'] = $avisosModel->getAllAvisos($_SESSION['id_privada']);
        $assets['styles'] = ['/css/Admin/admin_avisos.css'];
        $assets['scripts'] = ['/js/Admin/admin_avisos.js'];

        $view_to_load = 'avisos.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }

    public function operacion_Avisos($caso){
        $casoS = $caso;
        $avisosModel = new AvisosModel();
        $utilityModel = new UtilityModel();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); 
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        switch ($casoS){
            case 1: // CREAR AVISOS
                // Se obtiene el contenido JSON enviado desde el cliente (por ejemplo, desde fetch() en JS)
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
                exit;

            case 2: // ELIMINAR UN AVISO
                if (!isset($_SESSION['user_id']) || !isset($_POST['id_aviso'])) {
                    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
                    return;
                }

                try {
                    $id_aviso = (int)$_POST['id_aviso'];
                    $userPublicId = $_SESSION['user_id'];
                    $success = $avisosModel->deleteAviso($id_aviso, $userPublicId);

                    if ($success) {
                        echo json_encode(['success' => true, 'message' => 'Aviso eliminado.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar (¿Es tu aviso?).']);
                    }

                } catch (\Exception $e) {
                    error_log("Error deleteAviso: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Error inesperado.']);
                }                                
                exit;
        }
    }
    public function showConfigs()
    {
        // $usersModel = new UsersModel();
        // $users = $usersModel->getusers();

        // $data['users'] = $users;   
        
        // $assets['styles'] = ['/css/admin_users.css'];
        $assets['styles'][] = '/css/Admin/config.css';
        $assets['scripts'] = ['/js/Admin/admin_config.js'];
        $view_to_load = 'configs.php';
        
        require __DIR__ . '/../../Views/Admin/Panel.php';
    }
    public function procesa_Modal_Configuracion($modalName){
        //Define la ruta base para los modales de configuración
        $view_path = __DIR__ . '/../../Views/Admin/sections/sectionsconfig/';

        //Busca bajo tu ruta del js cual es el modal que llamaras
        $file_to_load = '';
        switch ($modalName) {
            case 'editar-perfil':
                $file_to_load = $view_path . 'editperfil.php';
                break;
            // case 'change-password':
            //     $file_to_load = $view_path . 'otromodal.php';
            //     break;
            default:
                http_response_code(404);
                echo "Modal no encontrado.";
                exit;
        }

        // 3. Carga el archivo (si existe)
        if (file_exists($file_to_load)) {
            // Aquí podrías cargar datos si el modal los necesita, por ej:
            // $userModel = new UserModel();
            // $data['perfil'] = $userModel->getProfile($_SESSION['user_id']);
            // extract($data);

            // Carga el archivo PHP. El iframe lo renderizará.
            require $file_to_load;
        } else {
            http_response_code(404);
            echo "Archivo de modal no encontrado: " . $file_to_load;
        }
        
        // Salimos para que no se renderice nada más
        exit;
    }
    public function loadContent($view)
    {
        // --- INICIO DE LA MODIFICACIÓN ---
        // Instanciamos el verificador y modelo
        $sessionModel = new SessionDataModel(); 
        $verifier = new SessionVerifier($sessionModel);
        //Dale una de las credenciales A verificar
        $keys_to_check = ['user_id'];
        //Enviale el nombre del archivo y unelo con .php (para la vista)
        $view_file = basename($view, '.php');
        //Define una lista de las vistas que lo ocupan
        $vistas_privadas = ['residents', 'colaboradores', 'dashboard']; 
        
        if (in_array($view_file, $vistas_privadas)) {
            $keys_to_check[] = 'id_privada'; //Le damos el UUDI a la vista si la requiere
        }

        //Verificamos
        if (!$verifier->verify($keys_to_check)) {
            http_response_code(401); // 401 Unauthorized, para rebotar
            
            // Devolvemos una respuesta JSON que el Javascript pueda entender
            echo json_encode([
                'success' => false, 
                'message' => 'Sesión inválida o expirada.',
                'errors' => $verifier->getErrors() // Opcional, para depuración
            ]);
            exit; // Detenemos la ejecución
        }

        $data = $this->cargarDatosDelPanel();
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
                $privadaModel = new UtilityModel();
                // $reportes = $dashboardModel->getReportes($_SESSION['id_privada']);
                //Renombre la funcion a otra por conflictos de llamada en este codigo
                $avisos = $dashboardModel->getAvisosD($_SESSION['id_privada']);
                // $data['reportes'] = $reportes;
                $data['avisos'] = $avisos;
                $data['paymentStatsJSON'] = json_encode($dashboardModel->obtenPagosStat($_SESSION['id_privada']));
                // $assets['styles'] = ['/css/Admin/admin_dashboard.css'];
                // $assets['scripts'][] = 'https://cdn.jsdelivr.net/npm/chart.js';
                $assets['styles'] = ['/css/Admin/admin_dashboard.css'];                
                $assets['scripts'] = ['https://cdn.jsdelivr.net/npm/chart.js'];
                // $assets['scripts'] = ['https://cdn.jsdelivr.net/npm/chart.js' , '/js/Admin/admin_dashboard.js'];

                break;
            case 'residents':
                $filter = $_GET['filter'] ?? 'owners';
                $residentsModel = new ResidentsModel();
                $utilityModel = new UtilityModel();
                $data['residents'] = $residentsModel->getAllResidents($_SESSION['id_privada'], $filter);
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
                $colaborador = $colaboradorModel->obtenColaboradores($_SESSION['id_privada'], $data['roles']);
                $data['colaboradores'] = $colaborador;
                $data['Pcolaboradores'] = $utilityModel->obtenTodosPrivadas();
                $data['colabestatus'] = $utilityModel->obtenPrimDatosEstatus();
                $assets['styles'][] = '/css/Admin/admin_colaboradores.css';
                $assets['scripts'] = ['/js/Admin/admin_colaboradoresAnadir.js' , '/js/Admin/admin_colaboradores.js'];
                break;
            case 'servicios':
                $serviciosModel = new ServiciosModel(); // DECLARO EL MODELO Y MODELO DE UTILIDAD ANTES PARA NO REPETIR EN CADA CASO Y OPTIMIZAR
                $utilityModel = new UtilityModel();
                //Almacena en un JSON los datos y el filtro definido
                $data['lista_proveedores'] = $serviciosModel->obtenProveedoresPorPrivada($_SESSION['id_privada']);
                $data['lista_servicios'] = $serviciosModel->obtenServiciosAsignados($_SESSION['id_privada']);

                $assets['styles'] = ['/css/Admin/admin_servicios.css'];
                $assets['scripts'] = ['/js/Admin/admin_servicios.js' , '/js/Admin/admin_proveedor.js'];
                break;
            case 'configs':
                $assets['styles'][] = '/css/Admin/config.css';
                $assets['scripts'] = ['/js/Admin/admin_config.js'];
                break;
            case 'avisos':
                $avisosModel = new AvisosModel();
                // Usamos $_SESSION['id_privada'] que contiene el UUID de la privada
                $data['avisos'] = $avisosModel->getAllAvisos($_SESSION['id_privada']);
                
                $assets['styles'] = ['/css/Admin/admin_avisos.css'];
                $assets['scripts'] = ['/js/Admin/admin_avisos.js',];
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

 
    

    


    
