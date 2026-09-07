<?php
require_once __DIR__ . '/../config/database.php';

class MaterialModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear un nuevo material
     */
    public function crear($datos) {
        try {
            // Validar datos obligatorios
            if (empty($datos['sku']) || empty($datos['nombre'])) {
                return ['success' => false, 'message' => 'SKU y Nombre son obligatorios'];
            }

            $sku = trim($datos['sku']);
            $nombre = trim($datos['nombre']);
            $familia = !empty($datos['familia']) ? trim($datos['familia']) : null;
            
            // UNIDAD DE CARGA - SOLO VALORES PERMITIDOS
            $unidadesPermitidas = ['UND', 'KG', 'LT', 'Caja', 'Pack', 'Pallet'];
            $unidad = !empty($datos['unidad_carga']) ? trim($datos['unidad_carga']) : 'UND';
            
            // Si la unidad no está en la lista, usar UND por defecto
            if (!in_array($unidad, $unidadesPermitidas)) {
                $unidad = 'UND';
            }

            // Verificar si el SKU ya existe
            $stmt = $this->db->prepare("SELECT id FROM materiales WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => "El SKU '{$sku}' ya existe en el sistema"];
            }

            // Insertar material
            $stmt = $this->db->prepare("
                INSERT INTO materiales (sku, nombre, familia, unidad_carga, activo)
                VALUES (?, ?, ?, ?, 1)
            ");
            
            $result = $stmt->execute([$sku, $nombre, $familia, $unidad]);

            if ($result) {
                return ['success' => true, 'message' => 'Material creado correctamente'];
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'message' => 'Error al insertar: ' . ($errorInfo[2] ?? 'Error desconocido')];
            }

        } catch (PDOException $e) {
            error_log("Error en crear material: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener material por SKU
     */
    public function obtenerPorSku($sku) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM materiales WHERE sku = ? AND activo = 1");
            $stmt->execute([$sku]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener material por SKU: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener material por ID
     */
    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM materiales WHERE id = ? AND activo = 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener material por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar materiales activos
     */
    public function listarActivos() {
        try {
            $stmt = $this->db->query("SELECT * FROM materiales WHERE activo = 1 ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar materiales activos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar todos los materiales
     */
    public function listarTodos() {
        try {
            $stmt = $this->db->query("SELECT * FROM materiales ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al listar materiales: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar material
     */
    public function actualizar($id, $datos) {
        try {
            // Validar datos
            if (empty($datos['sku']) || empty($datos['nombre'])) {
                return ['success' => false, 'message' => 'SKU y Nombre son obligatorios'];
            }

            $sku = trim($datos['sku']);
            $nombre = trim($datos['nombre']);
            $familia = !empty($datos['familia']) ? trim($datos['familia']) : null;
            
            // UNIDAD DE CARGA - SOLO VALORES PERMITIDOS
            $unidadesPermitidas = ['UND', 'KG', 'LT', 'Caja', 'Pack', 'Pallet'];
            $unidad = !empty($datos['unidad_carga']) ? trim($datos['unidad_carga']) : 'UND';
            
            if (!in_array($unidad, $unidadesPermitidas)) {
                $unidad = 'UND';
            }

            // Verificar si el material existe
            $stmt = $this->db->prepare("SELECT id FROM materiales WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Material no encontrado'];
            }

            // Verificar que el SKU no esté en uso por otro material
            $stmt = $this->db->prepare("SELECT id FROM materiales WHERE sku = ? AND id != ?");
            $stmt->execute([$sku, $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => "El SKU '{$sku}' ya está en uso por otro material"];
            }

            // Actualizar
            $stmt = $this->db->prepare("
                UPDATE materiales 
                SET sku = ?, nombre = ?, familia = ?, unidad_carga = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$sku, $nombre, $familia, $unidad, $id]);

            if ($result) {
                return ['success' => true, 'message' => 'Material actualizado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el material'];
            }

        } catch (PDOException $e) {
            error_log("Error en actualizar material: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar material (desactivar)
     */
    public function eliminar($id) {
        try {
            // Verificar si el material existe
            $stmt = $this->db->prepare("SELECT id FROM materiales WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Material no encontrado'];
            }

            $stmt = $this->db->prepare("UPDATE materiales SET activo = 0 WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                return ['success' => true, 'message' => 'Material eliminado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar el material'];
            }

        } catch (PDOException $e) {
            error_log("Error en eliminar material: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener familias
     */
    public function obtenerFamilias() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT familia FROM materiales WHERE activo = 1 AND familia IS NOT NULL ORDER BY familia");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener familias: " . $e->getMessage());
            return [];
        }
    }
}
?>