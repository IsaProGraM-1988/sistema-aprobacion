<?php
require_once __DIR__ . '/../config/database.php';

class PrecioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener precio lista para un SKU y subcanal específico
     * 
     */
    public function obtenerPrecioLista($sku, $subcanal) {
        try {
            // Primero intenta obtener el precio con fechas vigentes
            $stmt = $this->db->prepare("
                SELECT * FROM precios 
                WHERE sku = ? AND subcanal = ? 
                AND fecha_vigencia <= CURDATE() 
                AND fecha_termino >= CURDATE()
                LIMIT 1
            ");
            $stmt->execute([$sku, $subcanal]);
            $precio = $stmt->fetch();
            
            // Si no hay precio vigente, busca cualquier precio para ese SKU/subcanal
            if (!$precio) {
                $stmt = $this->db->prepare("
                    SELECT * FROM precios 
                    WHERE sku = ? AND subcanal = ? 
                    ORDER BY fecha_termino DESC
                    LIMIT 1
                ");
                $stmt->execute([$sku, $subcanal]);
                $precio = $stmt->fetch();
            }
            
            return $precio;
        } catch (PDOException $e) {
            error_log("Error al obtener precio lista: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los precios de un SKU
     */
    public function obtenerPreciosPorSku($sku) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM precios 
                WHERE sku = ? 
                ORDER BY fecha_vigencia DESC
            ");
            $stmt->execute([$sku]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener precios por SKU: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener precio por ID
     */
    public function obtenerPorId($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM precios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener precio por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo precio
     */
    public function crear($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO precios (sku, subcanal, precio_lista, fecha_vigencia, fecha_termino)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['sku'],
                $datos['subcanal'],
                $datos['precio_lista'],
                $datos['fecha_vigencia'],
                $datos['fecha_termino']
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear precio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar precio existente
     */
    public function actualizar($id, $datos) {
        try {
            $stmt = $this->db->prepare("
                UPDATE precios 
                SET precio_lista = ?, fecha_vigencia = ?, fecha_termino = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $datos['precio_lista'],
                $datos['fecha_vigencia'],
                $datos['fecha_termino'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar precio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar precio (desactivar lógicamente o eliminar físicamente)
     */
    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM precios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar precio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recomendar precios basados en descuento deseado
     */
    public function recomendarPrecios($precioLista, $descuentoDeseado) {
        $precioPropuesto = $precioLista * (1 - $descuentoDeseado / 100);
        
        $precioRedondeado = round($precioPropuesto, 1);
        $precioArriba = $precioRedondeado;
        $precioAbajo = $precioRedondeado;
        
        $descuentoArriba = (($precioLista - $precioArriba) / $precioLista) * 100;
        $descuentoAbajo = (($precioLista - $precioAbajo) / $precioLista) * 100;
        
        return [
            'precio_arriba' => $precioArriba,
            'descuento_arriba' => round($descuentoArriba, 1),
            'precio_abajo' => $precioAbajo,
            'descuento_abajo' => round($descuentoAbajo, 1)
        ];
    }

    /**
     * Verificar si existe precio para un SKU y subcanal
     */
    public function existePrecio($sku, $subcanal) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM precios 
                WHERE sku = ? AND subcanal = ?
            ");
            $stmt->execute([$sku, $subcanal]);
            $result = $stmt->fetch();
            return $result['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar existencia de precio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los precios (para administración)
     */
    public function obtenerTodos() {
        try {
            $stmt = $this->db->query("
                SELECT p.*, m.nombre as nombre_material
                FROM precios p
                LEFT JOIN materiales m ON p.sku = m.sku
                ORDER BY p.sku, p.subcanal
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener todos los precios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener precios por subcanal
     */
    public function obtenerPorSubcanal($subcanal) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, m.nombre as nombre_material
                FROM precios p
                LEFT JOIN materiales m ON p.sku = m.sku
                WHERE p.subcanal = ?
                ORDER BY p.sku
            ");
            $stmt->execute([$subcanal]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener precios por subcanal: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Agregar precios por defecto para un SKU nuevo
     * (Crea precios en todos los subcanales)
     */
    public function crearPreciosPorDefecto($sku, $precioBase) {
        $subcanales = ['Retail', 'Distribución', 'Otros'];
        $resultados = [];
        
        foreach ($subcanales as $subcanal) {
            // Ajustar precio según subcanal
            $precio = $precioBase;
            if ($subcanal === 'Distribución') {
                $precio = $precioBase * 0.95; // 5% menos
            } elseif ($subcanal === 'Otros') {
                $precio = $precioBase * 0.90; // 10% menos
            }
            
            $datos = [
                'sku' => $sku,
                'subcanal' => $subcanal,
                'precio_lista' => round($precio, 0),
                'fecha_vigencia' => date('Y-m-d'),
                'fecha_termino' => date('Y-m-d', strtotime('+1 year'))
            ];
            
            $resultados[$subcanal] = $this->crear($datos);
        }
        
        return $resultados;
    }
}
?>