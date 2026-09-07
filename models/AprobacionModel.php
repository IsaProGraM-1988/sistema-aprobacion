<?php
require_once __DIR__ . '/../config/database.php';

class AprobacionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPorAprobador($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, s.codigo_solicitud, s.nombre_cliente, s.fecha_creacion,
                       COUNT(d.id) as total_skus, s.descuento_promedio,
                       a.estado as estado_aprobacion, s.id as id_solicitud,
                       s.canal, s.subcanal, s.email_vendedor
                FROM aprobaciones a
                INNER JOIN solicitudes s ON a.id_solicitud = s.id
                LEFT JOIN solicitud_detalles d ON s.id = d.id_solicitud
                WHERE a.email_aprobador = ?
                GROUP BY a.id
                ORDER BY a.fecha_asignacion DESC
            ");
            $stmt->execute([$email]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener aprobaciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorSolicitudYAprobador($idSolicitud, $email) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM aprobaciones 
                WHERE id_solicitud = ? AND email_aprobador = ?
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([$idSolicitud, $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener aprobación: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($idAprobacion, $estado, $comentarios = null) {
        try {
            if ($comentarios !== null) {
                $stmt = $this->db->prepare("
                    UPDATE aprobaciones 
                    SET estado = ?, comentarios = ?, fecha_resolucion = NOW() 
                    WHERE id = ?
                ");
                return $stmt->execute([$estado, $comentarios, $idAprobacion]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE aprobaciones 
                    SET estado = ?, fecha_resolucion = NOW() 
                    WHERE id = ?
                ");
                return $stmt->execute([$estado, $idAprobacion]);
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de aprobación: " . $e->getMessage());
            return false;
        }
    }

    public function contarPendientesPorSolicitud($idSolicitud) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as pendientes 
                FROM aprobaciones 
                WHERE id_solicitud = ? AND estado = 'pendiente'
            ");
            $stmt->execute([$idSolicitud]);
            $result = $stmt->fetch();
            return $result['pendientes'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error al contar pendientes: " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerTramoActual($idSolicitud) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT tramo 
                FROM aprobaciones 
                WHERE id_solicitud = ? AND estado = 'pendiente'
                ORDER BY tramo ASC 
                LIMIT 1
            ");
            $stmt->execute([$idSolicitud]);
            $result = $stmt->fetch();
            return $result['tramo'] ?? null;
        } catch (PDOException $e) {
            error_log("Error al obtener tramo actual: " . $e->getMessage());
            return null;
        }
    }

    public function crearAprobacionesAutomaticas($idSolicitud) {
        try {
            error_log("🔍 Creando aprobaciones para solicitud: $idSolicitud");
            
            $stmt = $this->db->prepare("SELECT canal, descuento_promedio FROM solicitudes WHERE id = ?");
            $stmt->execute([$idSolicitud]);
            $solicitud = $stmt->fetch();
            
            if (!$solicitud) {
                error_log("❌ Solicitud no encontrada: $idSolicitud");
                return false;
            }
            
            error_log("📦 Canal: " . $solicitud['canal'] . " - Descuento: " . $solicitud['descuento_promedio']);
            
            $stmt = $this->db->prepare("
                SELECT * FROM config_aprobadores 
                WHERE canal = ?
                ORDER BY tramo, orden
            ");
            $stmt->execute([$solicitud['canal']]);
            $configs = $stmt->fetchAll();
            
            error_log("📦 Aprobadores encontrados para canal " . $solicitud['canal'] . ": " . count($configs));
            
            if (empty($configs)) {
                error_log("❌ No hay aprobadores configurados para el canal: " . $solicitud['canal']);
                return false;
            }
            
            $insertados = 0;
            foreach ($configs as $config) {
                // Verificar que no exista ya una aprobación
                $stmt = $this->db->prepare("
                    SELECT id FROM aprobaciones 
                    WHERE id_solicitud = ? AND email_aprobador = ? AND tramo = ?
                ");
                $stmt->execute([$idSolicitud, $config['email_aprobador'], $config['tramo']]);
                
                if (!$stmt->fetch()) {
                    $stmt = $this->db->prepare("
                        INSERT INTO aprobaciones (id_solicitud, tramo, email_aprobador, estado, fecha_asignacion)
                        VALUES (?, ?, ?, 'pendiente', NOW())
                    ");
                    if ($stmt->execute([$idSolicitud, $config['tramo'], $config['email_aprobador']])) {
                        $insertados++;
                        error_log("✅ Aprobación creada: Tramo " . $config['tramo'] . " - " . $config['email_aprobador']);
                    }
                } else {
                    error_log("⚠️ Aprobación ya existe: " . $config['email_aprobador']);
                }
            }
            
            error_log("✅ Total aprobaciones creadas: $insertados");
            return $insertados > 0;
        } catch (PDOException $e) {
            error_log("Error al crear aprobaciones automáticas: " . $e->getMessage());
            return false;
        }
    }
}
?>