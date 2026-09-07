<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/notificaciones.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../models/MaterialModel.php';
require_once __DIR__ . '/../models/PrecioModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/AprobacionModel.php';

class SolicitudController {
    private $solicitudModel;
    private $clienteModel;
    private $materialModel;
    private $precioModel;
    private $usuarioModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
            exit;
        }
        $this->solicitudModel = new SolicitudModel();
        $this->clienteModel = new ClienteModel();
        $this->materialModel = new MaterialModel();
        $this->precioModel = new PrecioModel();
        $this->usuarioModel = new UsuarioModel();
    }

    public function nueva() {
        $usuario = Session::getUsuario();
        $clientes = $this->clienteModel->listarActivos();
        if ($usuario['rol'] === 'kam') {
            $clientes = $this->clienteModel->listarPorSubcanal($usuario['subcanal']);
        }
        $materiales = $this->materialModel->listarActivos();
        ob_start();
        include __DIR__ . '/../views/solicitudes/nueva.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function misSolicitudes() {
        $usuario = Session::getUsuario();
        $solicitudes = $this->solicitudModel->obtenerPorUsuario($usuario['id']);
        ob_start();
        include __DIR__ . '/../views/solicitudes/mis_solicitudes.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function detalle($id = null) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'public/index.php?route=dashboard');
            exit;
        }
        $solicitud = $this->solicitudModel->obtenerConDetalles($id);
        if (!$solicitud) {
            Session::setMensaje('Solicitud no encontrada', 'danger');
            header('Location: ' . BASE_URL . 'public/index.php?route=dashboard');
            exit;
        }
        ob_start();
        include __DIR__ . '/../views/solicitudes/detalle.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function editar($id = null) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'public/index.php?route=solicitudes/mis-solicitudes');
            exit;
        }
        $solicitud = $this->solicitudModel->obtenerPorId($id);
        if (!$solicitud || (int)$solicitud['id_vendedor'] !== (int)Session::get('usuario_id')) {
            Session::setMensaje('No tiene permisos para editar esta solicitud', 'danger');
            header('Location: ' . BASE_URL . 'public/index.php?route=solicitudes/mis-solicitudes');
            exit;
        }
        if ($solicitud['estado'] !== 'borrador') {
            Session::setMensaje('Solo se pueden editar solicitudes en estado borrador', 'warning');
            header('Location: ' . BASE_URL . 'public/index.php?route=solicitudes/mis-solicitudes');
            exit;
        }
        $idSolicitud = $id;
        ob_start();
        include __DIR__ . '/../views/solicitudes/editar.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    // ============================================================
    // GUARDAR BORRADOR
    // ============================================================
    public function guardarBorrador() {
        header('Content-Type: application/json');
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            
            // VALIDAR CSRF
            if (!Session::validarTokenCSRF($datos['csrf_token'] ?? '')) {
                throw new Exception('Token de seguridad inválido. Recargue la página.');
            }

            $this->validarDatosSolicitud($datos);

            $usuario = Session::getUsuario();
            $cliente = $this->clienteModel->obtenerPorCodigo($datos['codigo_cliente']);
            if (!$cliente) {
                throw new Exception('Cliente no encontrado');
            }

            $skus = json_decode($datos['skus'], true);
            if (count($skus) > MAX_SKUS) {
                throw new Exception('Máximo ' . MAX_SKUS . ' SKU\'s permitidos');
            }

            $detallesValidados = [];
            foreach ($skus as $sku) {
                $material = $this->materialModel->obtenerPorSku($sku['sku']);
                if (!$material) {
                    throw new Exception("El SKU {$sku['sku']} no existe en el sistema");
                }
                $precio = $this->precioModel->obtenerPrecioLista($sku['sku'], $cliente['subcanal']);
                if (!$precio) {
                    throw new Exception("El SKU {$sku['sku']} no tiene precio asignado para el subcanal {$cliente['subcanal']}");
                }
                $detallesValidados[] = [
                    'material' => $material,
                    'precio' => $precio,
                    'sku_data' => $sku
                ];
            }

            $solicitudData = [
                'codigo_solicitud' => $this->generarCodigoSolicitud(),
                'id_vendedor' => $usuario['id'],
                'email_vendedor' => $usuario['email'],
                'id_cliente' => $cliente['id'],
                'codigo_cliente' => $datos['codigo_cliente'],
                'nombre_cliente' => $cliente['nombre'],
                'subcanal' => $cliente['subcanal'],
                'canal' => $cliente['canal'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_termino' => $datos['fecha_termino'],
                'motivo' => $datos['motivo'] ?? '',
                'estado' => 'borrador'
            ];

            $idSolicitud = $this->solicitudModel->crear($solicitudData);
            if (!$idSolicitud) {
                throw new Exception('Error al guardar la solicitud');
            }

            foreach ($detallesValidados as $item) {
                $detalle = [
                    'id_solicitud' => $idSolicitud,
                    'sku' => $item['sku_data']['sku'],
                    'nombre_material' => $item['material']['nombre'],
                    'familia' => $item['material']['familia'],
                    'unidad_carga' => $item['material']['unidad_carga'],
                    'precio_lista' => $item['precio']['precio_lista'],
                    'precio_propuesto' => $item['sku_data']['precio_propuesto'],
                    'porcentaje_descuento' => $this->calcularPorcentaje($item['precio']['precio_lista'], $item['sku_data']['precio_propuesto']),
                    'volumen_esperado' => $item['sku_data']['volumen_esperado']
                ];
                $this->solicitudModel->agregarDetalle($detalle);
            }

            registrarActividad($usuario['email'], 'Guardó borrador de solicitud', "Solicitud: {$solicitudData['codigo_solicitud']}");

            // DEVOLVER NUEVO TOKEN CSRF PARA ACTUALIZAR EN EL FRONTEND
            $nuevoToken = Session::generarTokenCSRF();

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud guardada como borrador',
                'id_solicitud' => $idSolicitud,
                'csrf_token' => $nuevoToken
            ]);
        } catch (Exception $e) {
            error_log("❌ Error en guardarBorrador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // ENVIAR SOLICITUD
    // ============================================================
    public function enviar() {
        header('Content-Type: application/json');
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            $idSolicitud = $datos['id_solicitud'] ?? 0;
            $csrfToken = $datos['csrf_token'] ?? '';

            error_log("📤 Enviando solicitud ID: $idSolicitud");
            error_log("🔑 Token recibido: " . $csrfToken);

            // VALIDAR CSRF
            if (!Session::validarTokenCSRF($csrfToken)) {
                error_log("❌ Token CSRF inválido");
                throw new Exception('Token de seguridad inválido. Recargue la página.');
            }

            if (!$idSolicitud) {
                throw new Exception('ID de solicitud no válido');
            }

            $solicitud = $this->solicitudModel->obtenerPorId($idSolicitud);
            if (!$solicitud) {
                throw new Exception('Solicitud no encontrada');
            }

            if ((int)$solicitud['id_vendedor'] !== (int)Session::get('usuario_id')) {
                throw new Exception('No tiene permisos para enviar esta solicitud');
            }

            if ($solicitud['estado'] !== 'borrador') {
                throw new Exception('La solicitud ya fue enviada o está en otro estado');
            }

            $detalles = $this->solicitudModel->obtenerDetalles($idSolicitud);
            if (empty($detalles)) {
                throw new Exception('La solicitud no tiene SKUs agregados');
            }

            // Calcular descuento promedio
            $descuentoPromedio = $this->calcularDescuentoPromedio($detalles);
            error_log("📊 Descuento promedio: $descuentoPromedio%");
            
            // Actualizar estado a 'en_aprobacion'
            $this->solicitudModel->actualizarEstado($idSolicitud, 'en_aprobacion', $descuentoPromedio);

            // CREAR APROBACIONES AUTOMÁTICAS
            $aprobacionModel = new AprobacionModel();
            $resultado = $aprobacionModel->crearAprobacionesAutomaticas($idSolicitud);
            
            error_log("📤 Resultado creación aprobaciones: " . ($resultado ? 'OK' : 'FALLÓ'));

            // Verificar si hay aprobaciones creadas
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM aprobaciones WHERE id_solicitud = ?");
            $stmt->execute([$idSolicitud]);
            $totalAprobaciones = $stmt->fetch()['total'];

            error_log("📦 Total aprobaciones creadas para solicitud $idSolicitud: $totalAprobaciones");

            // Si no hay aprobaciones, crear fallback
            if ($totalAprobaciones == 0) {
                $stmt = $db->prepare("
                    INSERT INTO aprobaciones (id_solicitud, tramo, email_aprobador, estado, fecha_asignacion)
                    SELECT ?, 1, email, 'pendiente', NOW()
                    FROM usuarios 
                    WHERE rol IN ('aprobador', 'admin') AND activo = 1
                    AND NOT EXISTS (
                        SELECT 1 FROM aprobaciones a 
                        WHERE a.id_solicitud = ? AND a.email_aprobador = usuarios.email
                    )
                ");
                $stmt->execute([$idSolicitud, $idSolicitud]);
                $totalAprobaciones = $stmt->rowCount();
                error_log("📦 Aprobaciones creadas por fallback: $totalAprobaciones");
            }

            // Notificar a los aprobadores
            $stmt = $db->prepare("
                SELECT DISTINCT email_aprobador 
                FROM aprobaciones 
                WHERE id_solicitud = ? AND estado = 'pendiente'
            ");
            $stmt->execute([$idSolicitud]);
            $aprobadores = $stmt->fetchAll();

            foreach ($aprobadores as $aprobador) {
                notificarAprobadorNuevaSolicitud($aprobador['email_aprobador'], $solicitud);
            }

            notificarVendedorSolicitud($solicitud['email_vendedor'], $solicitud['codigo_solicitud'], 'en_aprobacion');

            registrarActividad(Session::get('email'), 'Envió solicitud para aprobación', "Solicitud: {$solicitud['codigo_solicitud']}");

            //GENERAR NUEVO TOKEN PARA PRÓXIMAS ACCIONES
            $nuevoToken = Session::generarTokenCSRF();

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud enviada para aprobación correctamente',
                'aprobadores_notificados' => count($aprobadores),
                'csrf_token' => $nuevoToken
            ]);
        } catch (Exception $e) {
            error_log("❌ Error en enviar: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // FUNCIONES PRIVADAS
    // ============================================================

    private function validarDatosSolicitud($datos) {
        $camposObligatorios = ['codigo_cliente', 'skus', 'fecha_inicio', 'fecha_termino'];
        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo $campo es obligatorio");
            }
        }

        $fechaInicio = new DateTime($datos['fecha_inicio']);
        $fechaTermino = new DateTime($datos['fecha_termino']);
        $hoy = new DateTime();
        $hoy->modify('+1 day');

        if ($fechaInicio < $hoy) {
            throw new Exception('La fecha de inicio debe ser a partir de mañana');
        }

        $intervalo = $fechaInicio->diff($fechaTermino);
        if ($intervalo->days > MAX_DIAS_PROMOCION) {
            throw new Exception("La promoción no puede superar " . MAX_DIAS_PROMOCION . " días");
        }

        if ($fechaTermino < $fechaInicio) {
            throw new Exception('La fecha de término debe ser posterior a la fecha de inicio');
        }

        $skus = json_decode($datos['skus'], true);
        if (!is_array($skus) || count($skus) === 0) {
            throw new Exception('Debe agregar al menos un SKU');
        }

        foreach ($skus as $sku) {
            if (empty($sku['sku']) || !isset($sku['precio_propuesto']) || !isset($sku['volumen_esperado'])) {
                throw new Exception('Todos los SKU\'s deben tener precio y volumen');
            }
            if ($sku['precio_propuesto'] <= 0) {
                throw new Exception('El precio propuesto debe ser mayor a 0');
            }
            if ($sku['volumen_esperado'] <= 0) {
                throw new Exception('El volumen esperado debe ser mayor a 0');
            }
        }
    }

    private function calcularPorcentaje($precioLista, $precioPropuesto) {
        if ($precioLista <= 0) return 0;
        return round((($precioLista - $precioPropuesto) / $precioLista) * 100, 1);
    }

    private function calcularDescuentoPromedio($detalles) {
        $total = 0;
        $count = count($detalles);
        foreach ($detalles as $detalle) {
            $total += $detalle['porcentaje_descuento'];
        }
        return $count > 0 ? round($total / $count, 1) : 0;
    }

    private function determinarTramos($canal, $detalles, $descuentoPromedio) {
        $tramos = [1];
        $config = $this->solicitudModel->obtenerConfiguracionTramos($canal);
        foreach ($config as $conf) {
            if ($descuentoPromedio > $conf['descuento_max']) {
                $tramos[] = $conf['tramo'];
            }
        }
        return array_unique($tramos);
    }

    private function generarCodigoSolicitud() {
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "SOL-{$date}-{$random}";
    }
}
?>