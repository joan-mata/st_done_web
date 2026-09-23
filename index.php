<?php
// index.php — Front Controller
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/CarritoController.php';
require_once __DIR__ . '/controllers/PedidoController.php';
require_once __DIR__ . '/controllers/ExploradorDbController.php';

// ── Acciones POST ─────────────────────────────────────────────
$accion = $_GET['accion'] ?? '';

if ($accion !== '') {
    $accionesValidas = [
        'login', 'registro', 'logout',
        'agregar_carrito', 'actualizar_carrito', 'eliminar_carrito', 'vaciar_carrito',
        'confirmar_pedido', 'actualizar_perfil', 'solicitar_devolucion',
    ];
    if (!in_array($accion, $accionesValidas, true)) {
        header('Location: index.php');
        exit;
    }
    switch ($accion) {
        case 'login':              (new UsuarioController())->procesarLogin();       break;
        case 'registro':           (new UsuarioController())->procesarRegistro();    break;
        case 'logout':             (new UsuarioController())->logout();              break;
        case 'actualizar_perfil':  (new UsuarioController())->actualizarPerfil();   break;
        case 'agregar_carrito':    (new CarritoController())->agregarProducto();     break;
        case 'actualizar_carrito': (new CarritoController())->actualizarCantidad();  break;
        case 'eliminar_carrito':   (new CarritoController())->eliminarProducto();    break;
        case 'vaciar_carrito':     (new CarritoController())->vaciarCarrito();       break;
        case 'confirmar_pedido':   (new PedidoController())->confirmarPedido();      break;
        case 'solicitar_devolucion': (new PedidoController())->solicitarDevolucion(); break;
    }
    exit;
}

// ── Páginas GET ───────────────────────────────────────────────
$pagina = $_GET['pagina'] ?? 'catalogo';

$paginasValidas = [
    'catalogo', 'producto', 'carrito', 'checkout', 'login', 'registro',
    'perfil', 'confirmacion', 'logout', 'pedido', 'devolucion',
    'explorador_db',
];

if (!in_array($pagina, $paginasValidas, true)) {
    $error = 'Página no encontrada.';
    require __DIR__ . '/views/error.php';
    exit;
}

switch ($pagina) {
    case 'catalogo':     (new ProductoController())->mostrarCatalogo();        break;
    case 'producto':     (new ProductoController())->mostrarDetalle();         break;
    case 'carrito':      (new CarritoController())->mostrarCarrito();          break;
    case 'checkout':     (new PedidoController())->mostrarCheckout();          break;
    case 'login':        (new UsuarioController())->mostrarLogin();            break;
    case 'registro':     (new UsuarioController())->mostrarRegistro();         break;
    case 'perfil':       (new UsuarioController())->mostrarPerfil();           break;
    case 'confirmacion': (new PedidoController())->mostrarConfirmacion();      break;
    case 'logout':       (new UsuarioController())->logout();                  break;
    case 'pedido':       (new PedidoController())->mostrarDetallePedido();     break;
    case 'devolucion':   (new PedidoController())->mostrarDevolucion();        break;
    case 'explorador_db': (new ExploradorDbController())->mostrar();           break;
}
