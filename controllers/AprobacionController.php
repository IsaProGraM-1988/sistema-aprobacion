<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/notificaciones.php';
require_once __DIR__ . '/../models/SolicitudModel.php';
require_once __DIR__ . '/../models/AprobacionModel.php';

class AprobacionController {
    private $solicitudModel;
    private $aprobacionModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
            exit;
        }
        $this->solicitudModel = new SolicitudModel();
        $this->aprobacionModel = new AprobacionModel();
    }

    public function misAprobaciones() {
        $email = Session::get('email');
        $rol = Session::get('rol');
        
        if ($rol === 'admin') {
            $aprobaciones = $this->obtenerTodasAprobacionesPendientes();
        } else {
            $aprobaciones = $this->aprobacionModel->obtenerPorAprobador($email);
        }
        
        ob_start();
        include __DIR__ . '/../views/aprobaciones/mis_aprobaciones.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    private function obtenerTodasAprobacionesPendientes() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT a.*, s.codigo_solicitud, s.nombre_cliente, s.fecha_creacion,
                       COUNT(d.id) as total_skus, s.descuento_promedio,
                       a.estado as estado_aprobacion, s.id as id_solicitud,
                       s.canal, s.subcanal, s.email_vendedor
                FROM aprobaciones a
                INNER JOIN solicitudes s ON a.id_solicitud = s.id
                LEFT JOIN solicitud_detalles d ON s.id = d.id_solicitud
                WHERE a.estado = 'pendiente'
                GROUP BY a.id
                ORDER BY a.fecha_asignacion DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener todas las aprobaciones: " . $e->getMessage());
            return [];
        }
    }

    public function detalle($idSolicitud = null) {
        if (!$idSolicitud) {
            header('Location: ' . BASE_URL . 'public/index.php?route=aprobaciones/mis-aprobaciones');
            exit;
        }

        $email = Session::get('email');
        $rol = Session::get('rol');
        
        $solicitud = $this->solicitudModel->obtenerConDetalles($idSolicitud);
        if (!$solicitud) {
            Session::setMensaje('Solicitud no encontrada', 'danger');
            header('Location: ' . BASE_URL . 'public/index.php?route=aprobaciones/mis-aprobaciones');
            exit;
        }

        if ($rol === 'admin') {
            $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
            if (!$aprobacion) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    INSERT INTO aprobaciones (id_solicitud, tramo, email_aprobador, estado, fecha_asignacion)
                    VALUES (?, 1, ?, 'pendiente', NOW())
                ");
                $stmt->execute([$idSolicitud, $email]);
                $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
            }
        } else {
            $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
            if (!$aprobacion) {
                Session::setMensaje('No tienes permisos para ver esta aprobación', 'danger');
                header('Location: ' . BASE_URL . 'public/index.php?route=aprobaciones/mis-aprobaciones');
                exit;
            }
        }

        ob_start();
        include __DIR__ . '/../views/aprobaciones/detalle_aprobacion.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function aprobar() {
        header('Content-Type: application/json');
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            $idSolicitud = $datos['id_solicitud'] ?? 0;
            $comentarios = trim($datos['comentarios'] ?? '');
            $fechaTerminoNueva = $datos['fecha_termino'] ?? null;

            if (!Session::validarTokenCSRF($datos['csrf_token'] ?? '')) {
                throw new Exception('Token de seguridad inválido');
            }

            if (!$idSolicitud) {
                throw new Exception('ID de solicitud no válido');
            }

            if (empty($comentarios)) {
                throw new Exception('Debe ingresar un comentario al aprobar');
            }

            $email = Session::get('email');
            $rol = Session::get('rol');
            
            if ($rol === 'admin') {
                $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
                if (!$aprobacion) {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        INSERT INTO aprobaciones (id_solicitud, tramo, email_aprobador, estado, fecha_asignacion)
                        VALUES (?, 1, ?, 'pendiente', NOW())
                    ");
                    $stmt->execute([$idSolicitud, $email]);
                    $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
                }
            } else {
                $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
            }

            if (!$aprobacion || $aprobacion['estado'] !== 'pendiente') {
                throw new Exception('No tienes permisos o la aprobación ya fue resuelta');
            }

            // Actualizar aprobación
            $this->aprobacionModel->actualizarEstado($aprobacion['id'], 'aprobada', $comentarios);

            // Verificar si todas las aprobaciones pendientes fueron resueltas
            $pendientes = $this->aprobacionModel->contarPendientesPorSolicitud($idSolicitud);
            error_log("📊 Pendientes restantes para solicitud $idSolicitud: " . $pendientes);
            
            if ($pendientes === 0) {
                $db = Database::getInstance()->getConnection();
                
                // Verificar si hay alguna rechazada
                $stmt = $db->prepare("SELECT COUNT(*) as rechazadas FROM aprobaciones WHERE id_solicitud = ? AND estado = 'rechazada'");
                $stmt->execute([$idSolicitud]);
                $rechazadas = $stmt->fetch()['rechazadas'];

                if ($rechazadas > 0) {
                    $this->solicitudModel->actualizarEstado($idSolicitud, 'rechazada');
                    $solicitud = $this->solicitudModel->obtenerPorId($idSolicitud);
                    if ($solicitud) {
                        notificarVendedorSolicitud($solicitud['email_vendedor'], $solicitud['codigo_solicitud'], 'rechazada', $comentarios);
                    }
                    error_log("✅ Solicitud RECHAZADA - ID: " . $idSolicitud);
                } else {
                    // 🔥 ACTUALIZAR ESTADO A APROBADA
                    $this->solicitudModel->actualizarEstado($idSolicitud, 'aprobada');
                    if ($fechaTerminoNueva) {
                        $this->solicitudModel->actualizarFechaTermino($idSolicitud, $fechaTerminoNueva);
                    }
                    $solicitud = $this->solicitudModel->obtenerPorId($idSolicitud);
                    if ($solicitud) {
                        notificarVendedorSolicitud($solicitud['email_vendedor'], $solicitud['codigo_solicitud'], 'aprobada', $comentarios);
                    }
                    error_log("✅ Solicitud APROBADA - ID: " . $idSolicitud);
                }
            }

            registrarActividad($email, 'Aprobó solicitud', "Solicitud ID: $idSolicitud - Comentarios: $comentarios");

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud aprobada correctamente'
            ]);
        } catch (Exception $e) {
            error_log("❌ Error en aprobar: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function rechazar() {
        header('Content-Type: application/json');
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            $idSolicitud = $datos['id_solicitud'] ?? 0;
            $comentarios = trim($datos['comentarios'] ?? '');

            if (!Session::validarTokenCSRF($datos['csrf_token'] ?? '')) {
                throw new Exception('Token de seguridad inválido');
            }

            if (!$idSolicitud) {
                throw new Exception('ID de solicitud no válido');
            }

            if (empty($comentarios)) {
                throw new Exception('Debes ingresar un comentario al rechazar la solicitud');
            }

            $email = Session::get('email');
            $rol = Session::get('rol');
            
            if ($rol === 'admin') {
                $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
                if (!$aprobacion) {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        INSERT INTO aprobaciones (id_solicitud, tramo, email_aprobador, estado, fecha_asignacion)
                        VALUES (?, 1, ?, 'pendiente', NOW())
                    ");
                    $stmt->execute([$idSolicitud, $email]);
                    $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
                }
            } else {
                $aprobacion = $this->aprobacionModel->obtenerPorSolicitudYAprobador($idSolicitud, $email);
            }

            if (!$aprobacion || $aprobacion['estado'] !== 'pendiente') {
                throw new Exception('No tienes permisos o la aprobación ya fue resuelta');
            }

            // Actualizar aprobación a rechazada
            $this->aprobacionModel->actualizarEstado($aprobacion['id'], 'rechazada', $comentarios);
            
            // Cambiar estado de la solicitud a rechazada inmediatamente
            $this->solicitudModel->actualizarEstado($idSolicitud, 'rechazada');

            $solicitud = $this->solicitudModel->obtenerPorId($idSolicitud);
            if ($solicitud) {
                notificarVendedorSolicitud($solicitud['email_vendedor'], $solicitud['codigo_solicitud'], 'rechazada', $comentarios);
            }

            registrarActividad($email, 'Rechazó solicitud', "Solicitud ID: $idSolicitud - Comentarios: $comentarios");

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud rechazada correctamente'
            ]);
        } catch (Exception $e) {
            error_log("❌ Error en rechazar: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>