<?php
require_once __DIR__ . '/../config/database.php';

class SolicitudModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO solicitudes (
                    codigo_solicitud, id_vendedor, email_vendedor, id_cliente,
                    codigo_cliente, nombre_cliente, subcanal, canal,
                    fecha_inicio, fecha_termino, motivo, estado
                ) VALUES (
                    :codigo, :id_vendedor, :email, :id_cliente,
                    :codigo_cliente, :nombre_cliente, :subcanal, :canal,
                    :fecha_inicio, :fecha_termino, :motivo, :estado
                )
            ");
            
            $stmt->execute([
                ':codigo' => $datos['codigo_solicitud'],
                ':id_vendedor' => $datos['id_vendedor'],
                ':email' => $datos['email_vendedor'],
                ':id_cliente' => $datos['id_cliente'],
                ':codigo_cliente' => $datos['codigo_cliente'],
                ':nombre_cliente' => $datos['nombre_cliente'],
                ':subcanal' => $datos['subcanal'],
                ':canal' => $datos['canal'],
                ':fecha_inicio' => $datos['fecha_inicio'],
                ':fecha_termino' => $datos['fecha_termino'],
                ':motivo' => $datos['motivo'] ?? null,
                ':estado' => $datos['estado']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear solicitud: " . $e->getMessage());
            return false;
        }
    }

    public function agregarDetalle($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO solicitud_detalles (
                    id_solicitud, sku, nombre_material, familia, unidad_carga,
                    precio_lista, precio_propuesto, porcentaje_descuento, volumen_esperado
                ) VALUES (
                    :id_solicitud, :sku, :nombre, :familia, :unidad,
                    :precio_lista, :precio_propuesto, :porcentaje, :volumen
                )
            ");
            
            return $stmt->execute([
                ':id_solicitud' => $datos['id_solicitud'],
                ':sku' => $datos['sku'],
                ':nombre' => $datos['nombre_material'],
                ':familia' => $datos['familia'],
                ':unidad' => $datos['unidad_carga'],
                ':precio_lista' => $datos['precio_lista'],
                ':precio_propuesto' => $datos['precio_propuesto'],
                ':porcentaje' => $datos['porcentaje_descuento'],
                ':volumen' => $datos['volumen_esperado']
            ]);
        } catch (PDOException $e) {
            error_log("Error al agregar detalle: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM solicitudes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener solicitud: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerConDetalles($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       GROUP_CONCAT(d.sku) as skus_lista,
                       COUNT(d.id) as total_skus,
                       AVG(d.porcentaje_descuento) as descuento_promedio
                FROM solicitudes s
                LEFT JOIN solicitud_detalles d ON s.id = d.id_solicitud
                WHERE s.id = ?
                GROUP BY s.id
            ");
            $stmt->execute([$id]);
            $solicitud = $stmt->fetch();
            
            if ($solicitud) {
                $stmt = $this->db->prepare("SELECT * FROM solicitud_detalles WHERE id_solicitud = ?");
                $stmt->execute([$id]);
                $solicitud['detalles'] = $stmt->fetchAll();
            }
            
            return $solicitud;
        } catch (PDOException $e) {
            error_log("Error al obtener solicitud con detalles: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($id, $estado, $descuentoPromedio = null) {
        try {
            error_log("📝 Actualizando estado solicitud $id a: $estado");
            
            if ($descuentoPromedio !== null) {
                $sql = "UPDATE solicitudes SET estado = ?, descuento_promedio = ?, fecha_envio = NOW() WHERE id = ?";
                $params = [$estado, $descuentoPromedio, $id];
            } else {
                // Si el estado es 'aprobada' o 'rechazada', actualizar fecha_cierre
                if ($estado === 'aprobada' || $estado === 'rechazada') {
                    $sql = "UPDATE solicitudes SET estado = ?, fecha_cierre = NOW() WHERE id = ?";
                } else {
                    $sql = "UPDATE solicitudes SET estado = ? WHERE id = ?";
                }
                $params = [$estado, $id];
            }
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            error_log("✅ Estado actualizado: " . ($result ? 'OK' : 'FALLÓ'));
            return $result;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarFechaTermino($id, $fechaTermino) {
        try {
            $stmt = $this->db->prepare("UPDATE solicitudes SET fecha_termino = ? WHERE id = ?");
            return $stmt->execute([$fechaTermino, $id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar fecha término: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDetalles($idSolicitud) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM solicitud_detalles WHERE id_solicitud = ?");
            $stmt->execute([$idSolicitud]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener detalles: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorUsuario($idUsuario) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       COUNT(d.id) as total_skus,
                       AVG(d.porcentaje_descuento) as descuento_promedio
                FROM solicitudes s
                LEFT JOIN solicitud_detalles d ON s.id = d.id_solicitud
                WHERE s.id_vendedor = ?
                GROUP BY s.id
                ORDER BY s.fecha_creacion DESC
            ");
            $stmt->execute([$idUsuario]);
            $result = $stmt->fetchAll();
            error_log("📊 Solicitudes del usuario $idUsuario: " . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes del usuario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTodas() {
        try {
            $stmt = $this->db->query("
                SELECT s.*, 
                       COUNT(d.id) as total_skus,
                       AVG(d.porcentaje_descuento) as descuento_promedio,
                       u.nombre as nombre_vendedor,
                       u.apellido as apellido_vendedor
                FROM solicitudes s
                LEFT JOIN solicitud_detalles d ON s.id = d.id_solicitud
                LEFT JOIN usuarios u ON s.id_vendedor = u.id
                GROUP BY s.id
                ORDER BY s.fecha_creacion DESC
            ");
            $result = $stmt->fetchAll();
            error_log("📊 Total solicitudes obtenidas: " . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Error al obtener todas las solicitudes: " . $e->getMessage());
            return [];
        }
    }

    public function crearAprobacion($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO aprobaciones (id_solicitud, tramo, email_aprobador, estado)
                VALUES (:id_solicitud, :tramo, :email, :estado)
            ");
            
            return $stmt->execute([
                ':id_solicitud' => $datos['id_solicitud'],
                ':tramo' => $datos['tramo'],
                ':email' => $datos['email_aprobador'],
                ':estado' => $datos['estado']
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear aprobación: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerConfiguracionTramos($canal) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM config_tramos 
                WHERE canal = ? AND activo = 1
                ORDER BY tramo ASC
            ");
            $stmt->execute([$canal]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener configuración de tramos: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarDetalle($idDetalle, $idSolicitud) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM solicitud_detalles 
                WHERE id = ? AND id_solicitud = ?
            ");
            return $stmt->execute([$idDetalle, $idSolicitud]);
        } catch (PDOException $e) {
            error_log("Error al eliminar detalle: " . $e->getMessage());
            return false;
        }
    }
}
?>