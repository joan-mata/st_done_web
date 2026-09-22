<?php
// index.php — Front Controller
// ============================================================
// Punto de entrada único de la aplicación.
// Lee el parámetro ?pagina= o ?accion= y despacha al controlador
// correcto. Así toda la lógica pasa por aquí.
// ============================================================

// Arrancar la sesión de forma segura
session_start();

// Generar un token CSRF si no existe (se usa en todos los formularios)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Cargar controladores ─────────────────────────────────────
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/CarritoController.php';
require_once __DIR__ . '/controllers/PedidoController.php';

// ── Despachar acción (peticiones POST o acciones especiales) ──
$accion = $_GET['accion'] ?? '';

if ($accion !== '') {
    $accionesValidas = [
        'login', 'registro', 'logout',
        'agregar_carrito', 'actualizar_carrito', 'eliminar_carrito', 'vaciar_carrito',
        'confirmar_pedido',
    ];

    if (!in_array($accion, $accionesValidas, true)) {
        // Acción desconocida → ignorar y mostrar catálogo
        header('Location: index.php');
        exit;
    }

    switch ($accion) {
        case 'login':
            (new UsuarioController())->procesarLogin();
            break;
        case 'registro':
            (new UsuarioController())->procesarRegistro();
            break;
        case 'logout':
            (new UsuarioController())->logout();
            break;
        case 'agregar_carrito':
            (new CarritoController())->agregarProducto();
            break;
        case 'actualizar_carrito':
            (new CarritoController())->actualizarCantidad();
            break;
        case 'eliminar_carrito':
            (new CarritoController())->eliminarProducto();
            break;
        case 'vaciar_carrito':
            (new CarritoController())->vaciarCarrito();
            break;
        case 'confirmar_pedido':
            (new PedidoController())->confirmarPedido();
            break;
    }
    exit; // Por si el controlador no redirigió
}

// ── Despachar página (peticiones GET) ────────────────────────
$pagina = $_GET['pagina'] ?? 'catalogo';

$paginasValidas = ['catalogo', 'producto', 'carrito', 'checkout', 'login', 'registro', 'perfil', 'confirmacion', 'logout'];

if (!in_array($pagina, $paginasValidas, true)) {
    $pagina = 'error';
    $error  = 'Página no encontrada.';
    require __DIR__ . '/views/error.php';
    exit;
}

switch ($pagina) {
    case 'catalogo':
        (new ProductoController())->mostrarCatalogo();
        break;
    case 'producto':
        (new ProductoController())->mostrarDetalle();
        break;
    case 'carrito':
        (new CarritoController())->mostrarCarrito();
        break;
    case 'checkout':
        (new PedidoController())->mostrarCheckout();
        break;
    case 'login':
        (new UsuarioController())->mostrarLogin();
        break;
    case 'registro':
        (new UsuarioController())->mostrarRegistro();
        break;
    case 'perfil':
        (new UsuarioController())->mostrarPerfil();
        break;
    case 'confirmacion':
        (new PedidoController())->mostrarConfirmacion();
        break;
    case 'logout':
        (new UsuarioController())->logout();
        break;
}
