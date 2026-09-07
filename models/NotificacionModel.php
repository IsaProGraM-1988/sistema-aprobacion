<?php
require_once __DIR__ . '/../config/database.php';

class NotificacionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notificaciones (email_destino, titulo, mensaje, tipo)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['email_destino'],
                $datos['titulo'],
                $datos['mensaje'],
                $datos['tipo'] ?? 'info'
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorUsuario($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notificaciones 
                WHERE email_destino = ? 
                ORDER BY fecha_creacion DESC 
                LIMIT 50
            ");
            $stmt->execute([$email]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerNoLeidas($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notificaciones 
                WHERE email_destino = ? AND leido = 0
                ORDER BY fecha_creacion DESC
            ");
            $stmt->execute([$email]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener notificaciones no leídas: " . $e->getMessage());
            return [];
        }
    }

    public function marcarComoLeido($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notificaciones 
                SET leido = 1, fecha_lectura = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            return false;
        }
    }

    public function marcarTodasComoLeidas($email) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notificaciones 
                SET leido = 1, fecha_lectura = NOW() 
                WHERE email_destino = ? AND leido = 0
            ");
            return $stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("Error al marcar todas las notificaciones como leídas: " . $e->getMessage());
            return false;
        }
    }

    public function contarNoLeidas($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM notificaciones 
                WHERE email_destino = ? AND leido = 0
            ");
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error al contar notificaciones no leídas: " . $e->getMessage());
            return 0;
        }
    }
}
?>