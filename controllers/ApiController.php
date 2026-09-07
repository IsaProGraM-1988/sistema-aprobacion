<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../models/MaterialModel.php';
require_once __DIR__ . '/../models/PrecioModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';

class ApiController {
    private $clienteModel;
    private $materialModel;
    private $precioModel;
    private $solicitudModel;

    public function __construct() {
        header('Content-Type: application/json');
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado. Inicie sesión.']);
            exit;
        }
        $this->clienteModel = new ClienteModel();
        $this->materialModel = new MaterialModel();
        $this->precioModel = new PrecioModel();
        $this->solicitudModel = new SolicitudModel();
    }

    public function clientes() {
        $metodo = $_SERVER['REQUEST_METHOD'];
        if ($metodo === 'POST') {
            $datos = json_decode(file_get_contents('php://input'), true);
            if (isset($datos['codigo'])) {
                $cliente = $this->clienteModel->obtenerPorCodigo($datos['codigo']);
                if ($cliente) {
                    echo json_encode(['success' => true, 'data' => $cliente]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Código de cliente requerido']);
            }
        } else {
            $clientes = $this->clienteModel->listarActivos();
            echo json_encode(['success' => true, 'data' => $clientes]);
        }
    }

    public function materiales() {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (isset($datos['sku'])) {
            $material = $this->materialModel->obtenerPorSku($datos['sku']);
            if ($material) {
                $precio = null;
                if (isset($datos['subcanal'])) {
                    $precio = $this->precioModel->obtenerPrecioLista($datos['sku'], $datos['subcanal']);
                }
                echo json_encode([
                    'success' => true,
                    'data' => $material,
                    'precio' => $precio
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'SKU no encontrado']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'SKU requerido']);
        }
    }

    public function solicitud() {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de solicitud requerido']);
            return;
        }
        $solicitud = $this->solicitudModel->obtenerConDetalles($id);
        if ($solicitud) {
            echo json_encode(['success' => true, 'data' => $solicitud]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada']);
        }
    }

    public function precios() {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (isset($datos['sku']) && isset($datos['subcanal'])) {
            $precio = $this->precioModel->obtenerPrecioLista($datos['sku'], $datos['subcanal']);
            if ($precio) {
                echo json_encode(['success' => true, 'data' => $precio]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Precio no encontrado para este SKU y subcanal']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'SKU y subcanal requeridos']);
        }
    }

    public function calcularDescuento() {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (isset($datos['precio_lista']) && isset($datos['precio_propuesto'])) {
            $precioLista = floatval($datos['precio_lista']);
            $precioPropuesto = floatval($datos['precio_propuesto']);
            if ($precioLista > 0) {
                $descuento = (($precioLista - $precioPropuesto) / $precioLista) * 100;
                $descuento = round($descuento, 1);
                $recomendacion = $this->precioModel->recomendarPrecios($precioLista, $descuento);
                echo json_encode([
                    'success' => true,
                    'descuento' => $descuento,
                    'recomendacion' => $recomendacion
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Precio de lista inválido']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        }
    }

    public function tramos() {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (isset($datos['canal']) && isset($datos['descuento'])) {
            $tramos = $this->solicitudModel->obtenerConfiguracionTramos($datos['canal']);
            $descuento = floatval($datos['descuento']);
            $tramosAplicables = [];
            foreach ($tramos as $tramo) {
                if ($descuento > $tramo['descuento_max']) {
                    $tramosAplicables[] = $tramo['tramo'];
                }
            }
            echo json_encode([
                'success' => true,
                'tramos' => array_unique($tramosAplicables),
                'aprobacion_automatica' => $this->esAprobacionAutomatica($datos['canal'], $descuento)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Canal y descuento requeridos']);
        }
    }

    // Lógica consistente
    private function esAprobacionAutomatica($canal, $descuento) {
        $limiteAutomatico = $canal === 'Cobertura Nacional' ? 10 : 15;
        return $descuento <= $limiteAutomatico;
    }
}
?>