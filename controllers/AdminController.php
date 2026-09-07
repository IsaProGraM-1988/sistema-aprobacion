<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/notificaciones.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../models/MaterialModel.php';
require_once __DIR__ . '/../models/PrecioModel.php';

class AdminController {
    private $usuarioModel;
    private $solicitudModel;
    private $clienteModel;
    private $materialModel;
    private $precioModel;

    public function __construct() {
        if (!Session::isLoggedIn() || Session::get('rol') !== 'admin') {
            Session::setMensaje('No tiene permisos de administrador', 'danger');
            header('Location: ' . BASE_URL . 'public/index.php?route=dashboard');
            exit;
        }
        $this->usuarioModel = new UsuarioModel();
        $this->solicitudModel = new SolicitudModel();
        $this->clienteModel = new ClienteModel();
        $this->materialModel = new MaterialModel();
        $this->precioModel = new PrecioModel();
    }

    public function todasSolicitudes() {
        $solicitudes = $this->solicitudModel->obtenerTodas();
        ob_start();
        include __DIR__ . '/../views/admin/todas_solicitudes.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function configuracion() {
        $tramos = $this->obtenerConfiguracionTramos();
        $aprobadores = $this->obtenerConfiguracionAprobadores();
        $usuarios = $this->usuarioModel->listarTodos();
        $clientes = $this->clienteModel->listarTodos();
        $materiales = $this->materialModel->listarTodos();
        $precios = $this->obtenerPrecios();
        $subcanales = ['Retail', 'Distribución', 'Otros'];
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $error = 'Token de seguridad inválido';
            } else {
                $accion = $_POST['accion'] ?? '';
                switch ($accion) {
                    case 'guardar_tramo':
                        $result = $this->guardarConfiguracionTramo($_POST);
                        $success = $result ? 'Configuración de tramo guardada exitosamente' : 'Error al guardar la configuración del tramo';
                        break;
                    case 'eliminar_tramo':
                        $result = $this->eliminarConfiguracionTramo($_POST['id'] ?? 0);
                        $success = $result ? 'Configuración de tramo eliminada exitosamente' : 'Error al eliminar la configuración del tramo';
                        break;
                    case 'guardar_aprobador':
                        $result = $this->guardarConfiguracionAprobador($_POST);
                        $success = $result ? 'Aprobador configurado exitosamente' : 'Error al configurar el aprobador';
                        break;
                    case 'eliminar_aprobador':
                        $result = $this->eliminarConfiguracionAprobador($_POST['id'] ?? 0);
                        $success = $result ? 'Configuración de aprobador eliminada exitosamente' : 'Error al eliminar la configuración del aprobador';
                        break;
                    case 'cambiar_estado_usuario':
                        $result = $this->cambiarEstadoUsuario($_POST['id'] ?? 0, $_POST['activo'] ?? 0);
                        $success = $result ? 'Estado del usuario actualizado exitosamente' : 'Error al actualizar el estado del usuario';
                        break;
                }
            }
            $tramos = $this->obtenerConfiguracionTramos();
            $aprobadores = $this->obtenerConfiguracionAprobadores();
            $usuarios = $this->usuarioModel->listarTodos();
        }

        ob_start();
        include __DIR__ . '/../views/admin/configuracion.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    // ============================================================
    // GESTIÓN DE CLIENTES
    // ============================================================
    public function clientes() {
        $clientes = $this->clienteModel->listarTodos();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                Session::setMensaje('Token de seguridad inválido', 'danger');
                header('Location: ' . BASE_URL . 'public/index.php?route=admin/clientes');
                exit;
            }
            
            $accion = $_POST['accion'] ?? '';
            $resultado = null;
            
            try {
                if ($accion === 'agregar') {
                    if (empty($_POST['codigo']) || empty($_POST['nombre'])) {
                        throw new Exception('Código y Nombre son obligatorios');
                    }
                    
                    $datos = [
                        'codigo' => trim($_POST['codigo']),
                        'nombre' => trim($_POST['nombre']),
                        'subcanal' => $_POST['subcanal'] ?? '',
                        'canal' => $_POST['canal'] ?? '',
                        'pagador' => trim($_POST['pagador'] ?? '')
                    ];
                    
                    $resultado = $this->clienteModel->crear($datos);
                    
                    if ($resultado) {
                        Session::setMensaje('Cliente agregado exitosamente', 'success');
                    } else {
                        throw new Exception('Error al agregar cliente');
                    }
                    
                } elseif ($accion === 'editar') {
                    if (empty($_POST['id'])) {
                        throw new Exception('ID de cliente no proporcionado');
                    }
                    
                    $datos = [
                        'codigo' => trim($_POST['codigo']),
                        'nombre' => trim($_POST['nombre']),
                        'subcanal' => $_POST['subcanal'] ?? '',
                        'canal' => $_POST['canal'] ?? '',
                        'pagador' => trim($_POST['pagador'] ?? '')
                    ];
                    
                    $resultado = $this->clienteModel->actualizar($_POST['id'], $datos);
                    
                    if ($resultado) {
                        Session::setMensaje('Cliente actualizado exitosamente', 'success');
                    } else {
                        throw new Exception('Error al actualizar cliente');
                    }
                    
                } elseif ($accion === 'eliminar') {
                    if (empty($_POST['id'])) {
                        throw new Exception('ID de cliente no proporcionado');
                    }
                    
                    $resultado = $this->clienteModel->eliminar($_POST['id']);
                    
                    if ($resultado) {
                        Session::setMensaje('Cliente eliminado exitosamente', 'success');
                    } else {
                        throw new Exception('Error al eliminar cliente');
                    }
                }
                
            } catch (Exception $e) {
                error_log("❌ Error en clientes: " . $e->getMessage());
                Session::setMensaje($e->getMessage(), 'danger');
            }
            
            header('Location: ' . BASE_URL . 'public/index.php?route=admin/clientes');
            exit;
        }
        
        $clientes = $this->clienteModel->listarTodos();
        
        ob_start();
        include __DIR__ . '/../views/admin/clientes.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    // ============================================================
    // GESTIÓN DE MATERIALES
    // ============================================================
    public function materiales() {
        // Obtener lista de materiales
        $materiales = $this->materialModel->listarTodos();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar CSRF
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                Session::setMensaje('Token de seguridad inválido', 'danger');
                header('Location: ' . BASE_URL . 'public/index.php?route=admin/materiales');
                exit;
            }
            
            $accion = $_POST['accion'] ?? '';
            
            try {
                if ($accion === 'agregar') {
                    // Validar datos
                    if (empty($_POST['sku']) || empty($_POST['nombre'])) {
                        throw new Exception('SKU y Nombre son obligatorios');
                    }
                    
                    $datos = [
                        'sku' => trim($_POST['sku']),
                        'nombre' => trim($_POST['nombre']),
                        'familia' => trim($_POST['familia'] ?? ''),
                        'unidad_carga' => $_POST['unidad_carga'] ?? 'UND'
                    ];
                    
                    $resultado = $this->materialModel->crear($datos);
                    
                    if ($resultado['success']) {
                        Session::setMensaje($resultado['message'], 'success');
                    } else {
                        throw new Exception($resultado['message']);
                    }
                    
                } elseif ($accion === 'editar') {
                    if (empty($_POST['id'])) {
                        throw new Exception('ID de material no proporcionado');
                    }
                    
                    if (empty($_POST['sku']) || empty($_POST['nombre'])) {
                        throw new Exception('SKU y Nombre son obligatorios');
                    }
                    
                    $datos = [
                        'sku' => trim($_POST['sku']),
                        'nombre' => trim($_POST['nombre']),
                        'familia' => trim($_POST['familia'] ?? ''),
                        'unidad_carga' => $_POST['unidad_carga'] ?? 'UND'
                    ];
                    
                    $resultado = $this->materialModel->actualizar($_POST['id'], $datos);
                    
                    if ($resultado['success']) {
                        Session::setMensaje($resultado['message'], 'success');
                    } else {
                        throw new Exception($resultado['message']);
                    }
                    
                } elseif ($accion === 'eliminar') {
                    if (empty($_POST['id'])) {
                        throw new Exception('ID de material no proporcionado');
                    }
                    
                    $resultado = $this->materialModel->eliminar($_POST['id']);
                    
                    if ($resultado['success']) {
                        Session::setMensaje($resultado['message'], 'success');
                    } else {
                        throw new Exception($resultado['message']);
                    }
                }
                
            } catch (Exception $e) {
                error_log("❌ Error en materiales: " . $e->getMessage());
                Session::setMensaje($e->getMessage(), 'danger');
            }
            
            header('Location: ' . BASE_URL . 'public/index.php?route=admin/materiales');
            exit;
        }
        
        // Recargar materiales después de cualquier operación
        $materiales = $this->materialModel->listarTodos();
        
        ob_start();
        include __DIR__ . '/../views/admin/materiales.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    // ============================================================
    // GESTIÓN DE PRECIOS
    // ============================================================
    public function precios() {
        $precios = $this->obtenerPrecios();
        $materiales = $this->materialModel->listarTodos();
        $subcanales = ['Retail', 'Distribución', 'Otros'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                Session::setMensaje('Token de seguridad inválido', 'danger');
                header('Location: ' . BASE_URL . 'public/index.php?route=admin/precios');
                exit;
            }
            
            $accion = $_POST['accion'] ?? '';
            
            try {
                if ($accion === 'agregar') {
                    if (empty($_POST['sku']) || empty($_POST['subcanal']) || empty($_POST['precio_lista'])) {
                        throw new Exception('SKU, Subcanal y Precio son obligatorios');
                    }
                    
                    $datos = [
                        'sku' => $_POST['sku'],
                        'subcanal' => $_POST['subcanal'],
                        'precio_lista' => $_POST['precio_lista'],
                        'fecha_vigencia' => $_POST['fecha_vigencia'],
                        'fecha_termino' => $_POST['fecha_termino']
                    ];
                    
                    $resultado = $this->precioModel->crear($datos);
                    
                    if ($resultado) {
                        Session::setMensaje('Precio agregado exitosamente', 'success');
                    } else {
                        throw new Exception('Error al agregar precio. Verifique que no exista un precio para este SKU y subcanal.');
                    }
                    
                } elseif ($accion === 'editar') {
                    if (empty($_POST['id'])) {
                        throw new Exception('ID de precio no proporcionado');
                    }
                    
                    $datos = [
                        'precio_lista' => $_POST['precio_lista'],
                        'fecha_vigencia' => $_POST['fecha_vigencia'],
                        'fecha_termino' => $_POST['fecha_termino']
                    ];
                    
                    $resultado = $this->precioModel->actualizar($_POST['id'], $datos);
                    
                    if ($resultado) {
                        Session::setMensaje('Precio actualizado exitosamente', 'success');
                    } else {
                        throw new Exception('Error al actualizar precio');
                    }
                    
                } elseif ($accion === 'eliminar') {
                    if (empty($_POST['id'])) {
                        throw new Exception('ID de precio no proporcionado');
                    }
                    
                    $resultado = $this->precioModel->eliminar($_POST['id']);
                    
                    if ($resultado) {
                        Session::setMensaje('Precio eliminado exitosamente', 'success');
                    } else {
                        throw new Exception('Error al eliminar precio');
                    }
                }
                
            } catch (Exception $e) {
                error_log("❌ Error en precios: " . $e->getMessage());
                Session::setMensaje($e->getMessage(), 'danger');
            }
            
            header('Location: ' . BASE_URL . 'public/index.php?route=admin/precios');
            exit;
        }
        
        $precios = $this->obtenerPrecios();
        
        ob_start();
        include __DIR__ . '/../views/admin/precios.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    // ============================================================
    // FUNCIONES PRIVADAS
    // ============================================================

    private function obtenerConfiguracionTramos() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT * FROM config_tramos ORDER BY canal, tramo");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener configuración de tramos: " . $e->getMessage());
            return [];
        }
    }

    private function obtenerConfiguracionAprobadores() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT ca.*, u.nombre, u.apellido
                FROM config_aprobadores ca
                LEFT JOIN usuarios u ON ca.email_aprobador = u.email
                ORDER BY ca.canal, ca.tramo, ca.orden
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener configuración de aprobadores: " . $e->getMessage());
            return [];
        }
    }

    private function obtenerPrecios() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT p.*, m.nombre as nombre_material
                FROM precios p
                LEFT JOIN materiales m ON p.sku = m.sku
                ORDER BY p.sku, p.subcanal
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener precios: " . $e->getMessage());
            return [];
        }
    }

    private function guardarConfiguracionTramo($datos) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO config_tramos (canal, familia, tramo, descuento_min, descuento_max, aprobacion_automatica)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['canal'],
                $datos['familia'] ?? null,
                $datos['tramo'],
                $datos['descuento_min'],
                $datos['descuento_max'],
                isset($datos['aprobacion_automatica']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error al guardar configuración de tramo: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarConfiguracionTramo($id) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM config_tramos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar configuración de tramo: " . $e->getMessage());
            return false;
        }
    }

    private function guardarConfiguracionAprobador($datos) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO config_aprobadores (tramo, canal, email_aprobador, orden)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['tramo'],
                $datos['canal'],
                $datos['email_aprobador'],
                $datos['orden'] ?? 0
            ]);
        } catch (PDOException $e) {
            error_log("Error al guardar configuración de aprobador: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarConfiguracionAprobador($id) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM config_aprobadores WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar configuración de aprobador: " . $e->getMessage());
            return false;
        }
    }

    private function cambiarEstadoUsuario($id, $activo) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
            return $stmt->execute([$activo, $id]);
        } catch (PDOException $e) {
            error_log("Error al cambiar estado de usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>