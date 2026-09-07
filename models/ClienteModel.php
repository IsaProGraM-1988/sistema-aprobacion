<?php
require_once __DIR__ . '/../config/database.php';

class ClienteModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO clientes (codigo, nombre, subcanal, canal, pagador)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['codigo'],
                $datos['nombre'],
                $datos['subcanal'],
                $datos['canal'],
                $datos['pagador'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear cliente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorCodigo($codigo) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes WHERE codigo = ? AND activo = 1");
            $stmt->execute([$codigo]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener cliente por código: " . $e->getMessage());
            return false;
        }
    }

    public function listarActivos() {
        try {
            $stmt = $this->db->query("SELECT * FROM clientes WHERE activo = 1 ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar clientes activos: " . $e->getMessage());
            return [];
        }
    }

    public function listarTodos() {
        try {
            $stmt = $this->db->query("SELECT * FROM clientes ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar clientes: " . $e->getMessage());
            return [];
        }
    }

    public function listarPorSubcanal($subcanal) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clientes WHERE subcanal = ? AND activo = 1 ORDER BY nombre");
            $stmt->execute([$subcanal]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar clientes por subcanal: " . $e->getMessage());
            return [];
        }
    }

    public function actualizar($id, $datos) {
        try {
            $stmt = $this->db->prepare("
                UPDATE clientes 
                SET codigo = ?, nombre = ?, subcanal = ?, canal = ?, pagador = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $datos['codigo'],
                $datos['nombre'],
                $datos['subcanal'],
                $datos['canal'],
                $datos['pagador'] ?? null,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("UPDATE clientes SET activo = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }
}
?>