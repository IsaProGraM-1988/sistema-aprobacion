<?php
require_once __DIR__ . '/config.php';

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn() {
        return self::has('usuario_id') && self::has('email');
    }

    public static function getUsuario() {
        return [
            'id' => self::get('usuario_id'),
            'email' => self::get('email'),
            'nombre' => self::get('nombre'),
            'apellido' => self::get('apellido'),
            'rol' => self::get('rol'),
            'cargo' => self::get('cargo'),
            'subcanal' => self::get('subcanal')
        ];
    }

    public static function setUsuario($usuario) {
        self::set('usuario_id', $usuario['id']);
        self::set('email', $usuario['email']);
        self::set('nombre', $usuario['nombre']);
        self::set('apellido', $usuario['apellido']);
        self::set('rol', $usuario['rol']);
        self::set('cargo', $usuario['cargo'] ?? '');
        self::set('subcanal', $usuario['subcanal'] ?? '');
    }

    public static function setMensaje($mensaje, $tipo = 'info') {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = $tipo;
    }

    public static function getMensaje() {
        $mensaje = $_SESSION['mensaje'] ?? null;
        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }

    public static function generarTokenCSRF() {
        if (!self::has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            self::set('csrf_token', $token);
        }
        return self::get('csrf_token');
    }

    public static function validarTokenCSRF($token) {
        if (!self::has('csrf_token') || empty($token)) {
            return false;
        }
        // Usar hash_equals para prevenir timing attacks
        $valido = hash_equals(self::get('csrf_token'), $token);
        // Regenerar token después de validación exitosa
        if ($valido) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $valido;
    }

    public static function regenerarId() {
        session_regenerate_id(true);
    }
}

// Iniciar sesión automáticamente
Session::start();
?>