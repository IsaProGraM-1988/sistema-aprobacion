<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function manual() {
    // Mostrar página de manual sin necesidad de estar logueado
    include __DIR__ . '/../views/auth/manual.php';
    }

    public function login() {
        if (Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'public/index.php?route=dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Por favor, ingresa tu correo y contraseña';
            } else {
                $usuario = $this->usuarioModel->autenticar($email, $password);
                if ($usuario) {
                    Session::setUsuario($usuario);
                    Session::setMensaje('Bienvenido ' . $usuario['nombre'], 'success');
                    header('Location: ' . BASE_URL . 'public/index.php?route=dashboard');
                    exit;
                } else {
                    $error = 'Correo o contraseña incorrectos';
                }
            }
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        Session::destroy();
        header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
        exit;
    }

    public function perfil() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
            exit;
        }

        $usuario = $this->usuarioModel->obtenerPorId(Session::get('usuario_id'));
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $error = 'Token de seguridad inválido. Intente nuevamente.';
            } else {
                $nombre = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                $cargo = trim($_POST['cargo'] ?? '');

                if (empty($nombre) || empty($apellido)) {
                    $error = 'Nombre y apellido son obligatorios';
                } else {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, cargo = ? WHERE id = ?");
                    if ($stmt->execute([$nombre, $apellido, $cargo, Session::get('usuario_id')])) {
                        Session::set('nombre', $nombre);
                        Session::set('apellido', $apellido);
                        Session::set('cargo', $cargo);
                        $success = 'Perfil actualizado correctamente';
                        $usuario = $this->usuarioModel->obtenerPorId(Session::get('usuario_id'));
                    } else {
                        $error = 'Error al actualizar el perfil';
                    }
                }
            }
        }

        ob_start();
        include __DIR__ . '/../views/auth/perfil.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function cambiarPassword() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'public/index.php?route=auth/login');
            exit;
        }

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $error = 'Token de seguridad inválido';
            } else {
                $password_actual = $_POST['password_actual'] ?? '';
                $password_nueva = $_POST['password_nueva'] ?? '';
                $password_confirmar = $_POST['password_confirmar'] ?? '';

                if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
                    $error = 'Todos los campos son obligatorios';
                } elseif (strlen($password_nueva) < 6) {
                    $error = 'La nueva contraseña debe tener al menos 6 caracteres';
                } elseif ($password_nueva !== $password_confirmar) {
                    $error = 'Las contraseñas no coinciden';
                } else {
                    $usuario = $this->usuarioModel->obtenerPorId(Session::get('usuario_id'));
                    if (password_verify($password_actual, $usuario['password'])) {
                        $hashed = password_hash($password_nueva, PASSWORD_DEFAULT);
                        $db = Database::getInstance()->getConnection();
                        $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                        if ($stmt->execute([$hashed, Session::get('usuario_id')])) {
                            $success = 'Contraseña actualizada correctamente';
                        } else {
                            $error = 'Error al actualizar la contraseña';
                        }
                    } else {
                        $error = 'Contraseña actual incorrecta';
                    }
                }
            }
        }

        ob_start();
        include __DIR__ . '/../views/auth/cambiar_password.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }

    public function registrar() {
        if (!Session::isLoggedIn() || Session::get('rol') !== 'admin') {
            Session::setMensaje('No tiene permisos para acceder', 'danger');
            header('Location: ' . BASE_URL . 'public/index.php?route=dashboard');
            exit;
        }

        $error = null;
        $success = null;
        $roles = ['vendedor', 'aprobador', 'admin', 'kam'];
        $subcanales = ['Retail', 'Distribución', 'Otros'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            if (!Session::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $error = 'Token de seguridad inválido';
            } else {
                $datos = [
                    'email' => trim($_POST['email'] ?? ''),
                    'nombre' => trim($_POST['nombre'] ?? ''),
                    'apellido' => trim($_POST['apellido'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'password_confirmar' => $_POST['password_confirmar'] ?? '',
                    'rol' => $_POST['rol'] ?? 'vendedor',
                    'cargo' => trim($_POST['cargo'] ?? null),
                    'subcanal' => trim($_POST['subcanal'] ?? null)
                ];

                if (empty($datos['email']) || empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['password'])) {
                    $error = 'Todos los campos obligatorios deben estar completos';
                } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                    $error = 'Correo electrónico no válido';
                } elseif (strpos($datos['email'], '@quillayessurlat.cl') === false) {
                    $error = 'Debe usar un correo corporativo @quillayessurlat.cl';
                } elseif (strlen($datos['password']) < 6) {
                    $error = 'La contraseña debe tener al menos 6 caracteres';
                } elseif ($datos['password'] !== $datos['password_confirmar']) {
                    $error = 'Las contraseñas no coinciden';
                } elseif (!in_array($datos['rol'], $roles)) {
                    $error = 'Rol no válido';
                } else {
                    $existente = $this->usuarioModel->obtenerPorEmail($datos['email']);
                    if ($existente) {
                        $error = 'Este correo ya está registrado';
                    } else {
                        $id = $this->usuarioModel->crearUsuario($datos);
                        if ($id) {
                            $success = 'Usuario creado exitosamente';
                        } else {
                            $error = 'Error al crear el usuario';
                        }
                    }
                }
            }
        }

        ob_start();
        include __DIR__ . '/../views/auth/registrar.php';
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/main.php';
    }
}
?>