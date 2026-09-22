<?php
// controllers/UsuarioController.php
// ============================================================
// Controlador: gestiona login, registro y cierre de sesión.
// ============================================================

require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    private Usuario $modelo;

    public function __construct() {
        $this->modelo = new Usuario();
    }

    // Página: formulario de login
    public function mostrarLogin(): void {
        $error = '';
        require __DIR__ . '/../views/login.php';
    }

    // Acción: procesar el login (POST)
    public function procesarLogin(): void {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Error de seguridad. Vuelve a intentarlo.';
            require __DIR__ . '/../views/login.php';
            return;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validación básica de campos vacíos
        if ($email === '' || $password === '') {
            $error = 'Por favor, rellena todos los campos.';
            require __DIR__ . '/../views/login.php';
            return;
        }

        $usuario = $this->modelo->verificarCredenciales($email, $password);

        if (!$usuario) {
            // No decimos si es el email o la contraseña quien falla (seguridad)
            $error = 'Email o contraseña incorrectos.';
            require __DIR__ . '/../views/login.php';
            return;
        }

        // Login correcto: regenerar ID de sesión para evitar session fixation
        session_regenerate_id(true);

        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol']    = $usuario['rol'];

        // Redirigir al catálogo
        header('Location: index.php');
        exit;
    }

    // Página: formulario de registro
    public function mostrarRegistro(): void {
        $error = '';
        require __DIR__ . '/../views/registro.php';
    }

    // Acción: procesar el registro (POST)
    public function procesarRegistro(): void {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Error de seguridad. Vuelve a intentarlo.';
            require __DIR__ . '/../views/registro.php';
            return;
        }

        $nombre   = trim($_POST['nombre']    ?? '');
        $email    = trim($_POST['email']     ?? '');
        $password = trim($_POST['password']  ?? '');
        $password2= trim($_POST['password2'] ?? '');

        // Validaciones
        if ($nombre === '' || $email === '' || $password === '') {
            $error = 'Por favor, rellena todos los campos.';
            require __DIR__ . '/../views/registro.php';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El formato del email no es válido.';
            require __DIR__ . '/../views/registro.php';
            return;
        }

        if (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
            require __DIR__ . '/../views/registro.php';
            return;
        }

        if ($password !== $password2) {
            $error = 'Las contraseñas no coinciden.';
            require __DIR__ . '/../views/registro.php';
            return;
        }

        if ($this->modelo->existeEmail($email)) {
            $error = 'Ese email ya está registrado.';
            require __DIR__ . '/../views/registro.php';
            return;
        }

        $nuevoId = $this->modelo->crear($nombre, $email, $password);

        // Iniciar sesión directamente tras el registro
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $nuevoId;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_rol']    = 'cliente';

        header('Location: index.php');
        exit;
    }

    // Acción: cerrar sesión
    public function logout(): void {
        // Destruir la sesión completamente
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // Página: perfil del usuario (requiere estar autenticado)
    public function mostrarPerfil(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->getPorId($_SESSION['usuario_id']);

        require_once __DIR__ . '/../models/Pedido.php';
        $pedidoModel = new Pedido();
        $pedidos = $pedidoModel->getPorUsuario($_SESSION['usuario_id']);

        require __DIR__ . '/../views/perfil.php';
    }
}
