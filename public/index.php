<?php
// Definir la ruta base con RUTAS DINÁMICAS
define('BASE_PATH', dirname(__DIR__));

// Incluir configuración
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/session.php';

// Obtener ruta desde GET
$route = $_GET['route'] ?? 'dashboard';
$segments = explode('/', $route);

// MAPEO DE CONTROLADORES: URLs plurales a controladores singulares
$controllerMap = [
    'solicitudes' => 'SolicitudController',
    'aprobaciones' => 'AprobacionController',
    'admin' => 'AdminController',
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'api' => 'ApiController',
    'manual' => 'AuthController'
];

// Determinar el controlador
$controllerName = $controllerMap[$segments[0]] ?? (ucfirst($segments[0]) . 'Controller');

// Convertir el nombre del método: eliminar guiones y convertir a CamelCase
$action = $segments[1] ?? 'index';
$action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));

$params = array_slice($segments, 2);

// Manejar rutas API
if ($segments[0] === 'api') {
    $controllerName = 'ApiController';
    $action = $segments[1] ?? 'index';
    $action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));
    $params = array_slice($segments, 2);
}

// Ruta del controlador
$controllerFile = BASE_PATH . '/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    if (class_exists($controllerName)) {
        $controllerInstance = new $controllerName();
        if (method_exists($controllerInstance, $action)) {
            call_user_func_array([$controllerInstance, $action], $params);
        } else {
            http_response_code(404);
            if ($segments[0] === 'api') {
                echo json_encode(['success' => false, 'message' => 'Método API no encontrado: ' . $action]);
            } else {
                echo '<h2>Error 404: Método no encontrado</h2>';
                echo '<p>Controlador: ' . $controllerName . ' | Método: ' . $action . '</p>';
            }
        }
    } else {
        http_response_code(404);
        echo 'Error: Clase "' . $controllerName . '" no encontrada';
    }
} else {
    http_response_code(404);
    echo 'Error: Archivo de controlador no existe: ' . $controllerFile;
}
?>