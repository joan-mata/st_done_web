<?php
// controllers/UsuarioController.php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    private Usuario $modelo;

    public function __construct() {
        $this->modelo = new Usuario();
    }

    public function mostrarLogin(): void {
        $error = '';
        require __DIR__ . '/../views/login.php';
    }

    public function procesarLogin(): void {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Error de seguridad. Vuelve a intentarlo.';
            require __DIR__ . '/../views/login.php';
            return;
        }
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            $error = 'Por favor, rellena todos los campos.';
            require __DIR__ . '/../views/login.php';
            return;
        }
        $usuario = $this->modelo->verificarCredenciales($email, $password);
        if (!$usuario) {
            $error = 'Email o contraseña incorrectos.';
            require __DIR__ . '/../views/login.php';
            return;
        }
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol']    = $usuario['rol'];
        header('Location: index.php');
        exit;
    }

    public function mostrarRegistro(): void {
        $error = '';
        require __DIR__ . '/../views/registro.php';
    }

    public function procesarRegistro(): void {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Error de seguridad.';
            require __DIR__ . '/../views/registro.php';
            return;
        }
        $nombre    = trim($_POST['nombre']    ?? '');
        $email     = trim($_POST['email']     ?? '');
        $password  = trim($_POST['password']  ?? '');
        $password2 = trim($_POST['password2'] ?? '');

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
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $nuevoId;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_rol']    = 'cliente';
        header('Location: index.php');
        exit;
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // Página: perfil con dos pestañas (datos | pedidos)
    public function mostrarPerfil(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        $usuario = $this->modelo->getPorId($_SESSION['usuario_id']);
        $tab     = $_GET['tab'] ?? 'datos';           // 'datos' o 'pedidos'
        $exito   = $_SESSION['perfil_exito'] ?? '';
        $error   = $_SESSION['perfil_error'] ?? '';
        unset($_SESSION['perfil_exito'], $_SESSION['perfil_error']);

        require_once __DIR__ . '/../models/Pedido.php';
        $modeloPedido = new Pedido();
        $pedidos      = $modeloPedido->getPorUsuario($_SESSION['usuario_id']);

        // Actualizar estado automático de todos los pedidos del usuario
        foreach ($pedidos as &$p) {
            $p['estado'] = $modeloPedido->actualizarEstadoAutomatico($p['id']);
        }
        unset($p);

        require __DIR__ . '/../views/perfil.php';
    }

    // Acción: guardar cambios del perfil (POST)
    public function actualizarPerfil(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['perfil_error'] = 'Error de seguridad.';
            header('Location: index.php?pagina=perfil&tab=datos');
            exit;
        }
        $nombre       = trim($_POST['nombre']        ?? '');
        $email        = trim($_POST['email']         ?? '');
        $telefono     = trim($_POST['telefono']      ?? '');
        $direccion    = trim($_POST['direccion']     ?? '');
        $ciudad       = trim($_POST['ciudad']        ?? '');
        $codigoPostal = trim($_POST['codigo_postal'] ?? '');

        if ($nombre === '' || $email === '') {
            $_SESSION['perfil_error'] = 'El nombre y el email son obligatorios.';
            header('Location: index.php?pagina=perfil&tab=datos');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['perfil_error'] = 'El formato del email no es válido.';
            header('Location: index.php?pagina=perfil&tab=datos');
            exit;
        }

        $ok = $this->modelo->actualizar(
            $_SESSION['usuario_id'],
            $nombre, $email, $telefono, $direccion, $ciudad, $codigoPostal
        );

        if (!$ok) {
            $_SESSION['perfil_error'] = 'Ese email ya está en uso por otra cuenta.';
        } else {
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['perfil_exito']   = 'Perfil actualizado correctamente.';
        }
        header('Location: index.php?pagina=perfil&tab=datos');
        exit;
    }
}
