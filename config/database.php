<?php
class Database {
    private $host = "localhost";
    private $db_name = "sistema_aprobacion_quillayes";
    private $username = "root";
    private $password = "";
    private $conn;
    private static $instance = null;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Configurar zona horaria
            try {
                $this->conn->exec("SET time_zone = '-03:00'");
            } catch (PDOException $e) {
                $this->conn->exec("SET time_zone = '+00:00'");
            }
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>