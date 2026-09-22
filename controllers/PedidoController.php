<?php
// controllers/PedidoController.php
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Devolucion.php';

class PedidoController {

    // Página: checkout con formulario completo
    public function mostrarCheckout(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        if (empty($_SESSION['carrito'])) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        // Cargar datos del usuario para pre-rellenar el formulario
        require_once __DIR__ . '/../models/Usuario.php';
        $modeloUsuario = new Usuario();
        $usuario = $modeloUsuario->getPorId($_SESSION['usuario_id']);

        $carrito = $_SESSION['carrito'];
        $error   = '';
        require __DIR__ . '/../views/checkout.php';
    }

    // Acción: confirmar pedido (POST)
    public function confirmarPedido(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Error de seguridad.';
            $carrito = $_SESSION['carrito'] ?? [];
            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $datosEnvio = [
            'nombre_destinatario' => trim($_POST['nombre_destinatario'] ?? ''),
            'telefono_envio'      => trim($_POST['telefono_envio']      ?? ''),
            'calle'               => trim($_POST['calle']               ?? ''),
            'ciudad'              => trim($_POST['ciudad']              ?? ''),
            'codigo_postal'       => trim($_POST['codigo_postal']       ?? ''),
            'pais'                => trim($_POST['pais']                ?? 'España'),
        ];

        // Validar campos obligatorios de envío
        if ($datosEnvio['nombre_destinatario'] === '' ||
            $datosEnvio['calle'] === '' ||
            $datosEnvio['ciudad'] === '' ||
            $datosEnvio['codigo_postal'] === '') {
            $error   = 'Por favor, rellena todos los campos obligatorios de envío.';
            $carrito = $_SESSION['carrito'] ?? [];
            require_once __DIR__ . '/../models/Usuario.php';
            $usuario = (new Usuario())->getPorId($_SESSION['usuario_id']);
            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $carrito = $_SESSION['carrito'] ?? [];
        if (empty($carrito)) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        // Verificar stock antes de confirmar
        $modeloProducto = new Producto();
        foreach ($carrito as $item) {
            $producto = $modeloProducto->getPorId($item['producto_id']);
            if (!$producto || $producto['stock'] < $item['cantidad']) {
                $error = 'Stock insuficiente para: ' . htmlspecialchars($item['nombre']);
                require __DIR__ . '/../models/Usuario.php';
                $usuario = (new Usuario())->getPorId($_SESSION['usuario_id']);
                require __DIR__ . '/../views/checkout.php';
                return;
            }
        }

        try {
            $modeloPedido = new Pedido();
            $pedidoId = $modeloPedido->crear(
                $_SESSION['usuario_id'],
                $datosEnvio,
                array_values($carrito)
            );
            foreach ($carrito as $item) {
                $modeloProducto->reducirStock($item['producto_id'], $item['cantidad']);
            }
            $_SESSION['carrito']          = [];
            $_SESSION['ultimo_pedido_id'] = $pedidoId;
            header('Location: index.php?pagina=confirmacion');
            exit;

        } catch (RuntimeException $e) {
            $error   = 'No se pudo procesar el pedido. Inténtalo de nuevo.';
            $carrito = $_SESSION['carrito'] ?? [];
            require __DIR__ . '/../models/Usuario.php';
            $usuario = (new Usuario())->getPorId($_SESSION['usuario_id']);
            require __DIR__ . '/../views/checkout.php';
        }
    }

    // Página: confirmación tras el pedido
    public function mostrarConfirmacion(): void {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['ultimo_pedido_id'])) {
            header('Location: index.php');
            exit;
        }
        $pedidoId = (int)$_SESSION['ultimo_pedido_id'];
        unset($_SESSION['ultimo_pedido_id']);

        $modeloPedido = new Pedido();
        $pedido = $modeloPedido->getDetalle($pedidoId, $_SESSION['usuario_id']);
        if (!$pedido) {
            header('Location: index.php');
            exit;
        }
        require __DIR__ . '/../views/confirmacion.php';
    }

    // Página: detalle de un pedido con seguimiento
    public function mostrarDetallePedido(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        $pedidoId = (int)($_GET['id'] ?? 0);
        if ($pedidoId <= 0) {
            header('Location: index.php?pagina=perfil&tab=pedidos');
            exit;
        }

        $modeloPedido = new Pedido();

        // Actualizar estado automáticamente según el tiempo transcurrido
        $modeloPedido->actualizarEstadoAutomatico($pedidoId);

        $pedido = $modeloPedido->getDetalle($pedidoId, $_SESSION['usuario_id']);
        if (!$pedido) {
            $error = 'Pedido no encontrado.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        $puedeDevolver = $modeloPedido->puedeDevolver($pedido['cabecera']);

        // Obtener devolución si existe
        $modeloDevolucion = new Devolucion();
        $devolucion = $modeloDevolucion->getPorPedido($pedidoId);

        require __DIR__ . '/../views/detalle_pedido.php';
    }

    // Página: formulario de devolución
    public function mostrarDevolucion(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        $pedidoId = (int)($_GET['pedido_id'] ?? 0);
        if ($pedidoId <= 0) {
            header('Location: index.php?pagina=perfil&tab=pedidos');
            exit;
        }

        $modeloPedido = new Pedido();
        $pedido = $modeloPedido->getDetalle($pedidoId, $_SESSION['usuario_id']);

        if (!$pedido || !$modeloPedido->puedeDevolver($pedido['cabecera'])) {
            $error = 'No es posible solicitar la devolución de este pedido.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        $error = '';
        require __DIR__ . '/../views/devolucion.php';
    }

    // Acción: procesar solicitud de devolución (POST)
    public function solicitarDevolucion(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: index.php?pagina=perfil&tab=pedidos');
            exit;
        }

        $pedidoId    = (int)($_POST['pedido_id'] ?? 0);
        $motivo      = trim($_POST['motivo']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($pedidoId <= 0 || $motivo === '') {
            header('Location: index.php?pagina=devolucion&pedido_id=' . $pedidoId);
            exit;
        }

        // Verificar que puede devolver
        $modeloPedido = new Pedido();
        $pedido = $modeloPedido->getDetalle($pedidoId, $_SESSION['usuario_id']);

        if (!$pedido || !$modeloPedido->puedeDevolver($pedido['cabecera'])) {
            $error = 'No es posible devolver este pedido.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        try {
            $modeloDevolucion = new Devolucion();
            $modeloDevolucion->crear($pedidoId, $motivo, $descripcion);
            header('Location: index.php?pagina=pedido&id=' . $pedidoId);
            exit;
        } catch (RuntimeException $e) {
            $error = 'No se pudo procesar la devolución.';
            require __DIR__ . '/../views/error.php';
        }
    }
}
