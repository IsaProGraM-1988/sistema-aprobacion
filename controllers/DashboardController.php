<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/SolicitudModel.php';

class DashboardController {
    private $solicitudModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
            exit;
        }
        
        $this->solicitudModel = new SolicitudModel();
    }

    public function index() {
        try {
            $usuario = Session::getUsuario();
            
            if (!$usuario || !isset($usuario['id'])) {
                header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
                exit;
            }
            
            if ($usuario['rol'] === 'admin') {
                $estadisticas = $this->getEstadisticasAdmin();
                $ultimasSolicitudes = $this->solicitudModel->obtenerTodas();
                $solicitudesMensuales = $this->getSolicitudesMensualesAdmin();
            } else {
                $estadisticas = $this->getEstadisticasUsuario($usuario['id']);
                $ultimasSolicitudes = $this->solicitudModel->obtenerPorUsuario($usuario['id']);
                $solicitudesMensuales = $this->getSolicitudesMensualesUsuario($usuario['id']);
            }

            if (is_array($ultimasSolicitudes)) {
                $ultimasSolicitudes = array_slice($ultimasSolicitudes, 0, 5);
            } else {
                $ultimasSolicitudes = [];
            }

            ob_start();
            include __DIR__ . '/../views/dashboard/index.php';
            $content = ob_get_clean();
            include __DIR__ . '/../views/layouts/main.php';
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            error_log("Error en Dashboard: " . $e->getMessage());
        }
    }

    private function getEstadisticasAdmin() {
        try {
            $db = Database::getInstance()->getConnection();
            
            // CONTAR TODOS LOS ESTADOS
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'enviada' THEN 1 ELSE 0 END) as enviadas,
                    SUM(CASE WHEN estado = 'en_aprobacion' THEN 1 ELSE 0 END) as en_aprobacion,
                    SUM(CASE WHEN estado = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                    SUM(CASE WHEN estado = 'aprobada_parcial' THEN 1 ELSE 0 END) as aprobada_parcial,
                    SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                    SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as borradores
                FROM solicitudes
            ");
            $result = $stmt->fetch();
            
            // REGISTRAR EN LOG PARA DEPURACIÓN
            error_log("📊 Estadísticas Dashboard Admin: " . json_encode($result));
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'enviadas' => (int)($result['enviadas'] ?? 0),
                'en_aprobacion' => (int)($result['en_aprobacion'] ?? 0),
                'aprobadas' => (int)($result['aprobadas'] ?? 0),
                'aprobada_parcial' => (int)($result['aprobada_parcial'] ?? 0),
                'rechazadas' => (int)($result['rechazadas'] ?? 0),
                'borradores' => (int)($result['borradores'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error en estadísticas admin: " . $e->getMessage());
            return ['total' => 0, 'enviadas' => 0, 'en_aprobacion' => 0, 'aprobadas' => 0, 'rechazadas' => 0, 'borradores' => 0];
        }
    }

    private function getEstadisticasUsuario($idUsuario) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'enviada' THEN 1 ELSE 0 END) as enviadas,
                    SUM(CASE WHEN estado = 'en_aprobacion' THEN 1 ELSE 0 END) as en_aprobacion,
                    SUM(CASE WHEN estado = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                    SUM(CASE WHEN estado = 'aprobada_parcial' THEN 1 ELSE 0 END) as aprobada_parcial,
                    SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                    SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as borradores
                FROM solicitudes 
                WHERE id_vendedor = ?
            ");
            $stmt->execute([$idUsuario]);
            $result = $stmt->fetch();
            
            error_log("📊 Estadísticas Dashboard Usuario $idUsuario: " . json_encode($result));
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'enviadas' => (int)($result['enviadas'] ?? 0),
                'en_aprobacion' => (int)($result['en_aprobacion'] ?? 0),
                'aprobadas' => (int)($result['aprobadas'] ?? 0),
                'aprobada_parcial' => (int)($result['aprobada_parcial'] ?? 0),
                'rechazadas' => (int)($result['rechazadas'] ?? 0),
                'borradores' => (int)($result['borradores'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error en estadísticas usuario: " . $e->getMessage());
            return ['total' => 0, 'enviadas' => 0, 'en_aprobacion' => 0, 'aprobadas' => 0, 'rechazadas' => 0, 'borradores' => 0];
        }
    }

    private function getSolicitudesMensualesAdmin() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->query("
                SELECT 
                    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM solicitudes
                WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                ORDER BY mes ASC
            ");
            $resultados = $stmt->fetchAll();
            
            $meses = [];
            $cantidades = [];
            
            foreach ($resultados as $row) {
                $meses[] = date('M Y', strtotime($row['mes'] . '-01'));
                $cantidades[] = (int)$row['total'];
            }
            
            return ['meses' => $meses, 'cantidades' => $cantidades];
        } catch (PDOException $e) {
            error_log("Error en solicitudes mensuales admin: " . $e->getMessage());
            return ['meses' => [], 'cantidades' => []];
        }
    }

    private function getSolicitudesMensualesUsuario($idUsuario) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT 
                    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM solicitudes
                WHERE id_vendedor = ? 
                    AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                ORDER BY mes ASC
            ");
            $stmt->execute([$idUsuario]);
            $resultados = $stmt->fetchAll();
            
            $meses = [];
            $cantidades = [];
            
            foreach ($resultados as $row) {
                $meses[] = date('M Y', strtotime($row['mes'] . '-01'));
                $cantidades[] = (int)$row['total'];
            }
            
            return ['meses' => $meses, 'cantidades' => $cantidades];
        } catch (PDOException $e) {
            error_log("Error en solicitudes mensuales usuario: " . $e->getMessage());
            return ['meses' => [], 'cantidades' => []];
        }
    }
}
?>