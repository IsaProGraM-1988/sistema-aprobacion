<?php
require_once __DIR__ . '/../config/database.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function autenticar($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $stmt->execute([$usuario['id']]);
                
                $this->registrarLog($email, 'Inicio de sesión exitoso', $_SERVER['REMOTE_ADDR']);
                
                return $usuario;
            }
            
            $this->registrarLog($email, 'Intento de inicio de sesión fallido', $_SERVER['REMOTE_ADDR']);
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }

    public function crearUsuario($datos) {
        try {
            $hashedPassword = password_hash($datos['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (email, nombre, apellido, password, rol, cargo, subcanal) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $datos['email'],
                $datos['nombre'],
                $datos['apellido'],
                $hashedPassword,
                $datos['rol'] ?? 'vendedor',
                $datos['cargo'] ?? null,
                $datos['subcanal'] ?? null
            ]);

            if ($result) {
                $id = $this->db->lastInsertId();
                $this->registrarLog($datos['email'], 'Usuario creado', $_SERVER['REMOTE_ADDR']);
                return $id;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }

    public function listarTodos() {
        try {
            $stmt = $this->db->query("SELECT * FROM usuarios ORDER BY nombre, apellido");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar usuarios: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerAprobadoresPorTramo($tramo, $canal) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, ca.tramo, ca.orden 
                FROM config_aprobadores ca 
                INNER JOIN usuarios u ON ca.email_aprobador = u.email 
                WHERE ca.tramo = ? AND ca.canal = ? AND ca.activo = 1 AND u.activo = 1
                ORDER BY ca.orden ASC
            ");
            $stmt->execute([$tramo, $canal]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener aprobadores: " . $e->getMessage());
            return [];
        }
    }

    private function registrarLog($email, $accion, $ip) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs_sistema (email_usuario, accion, ip, fecha) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$email, $accion, $ip]);
        } catch (PDOException $e) {
            error_log("Error al registrar log: " . $e->getMessage());
        }
    }
}
?>