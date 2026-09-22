<?php
// controllers/PedidoController.php
// ============================================================
// Controlador: gestiona el proceso de checkout y confirmación.
// ============================================================

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Producto.php';

class PedidoController {

    // Página: formulario de checkout
    public function mostrarCheckout(): void {
        // Solo usuarios autenticados pueden llegar aquí
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }

        // El carrito no puede estar vacío
        if (empty($_SESSION['carrito'])) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        $carrito = $_SESSION['carrito'];
        $error   = '';
        require __DIR__ . '/../views/checkout.php';
    }

    // Acción: confirmar el pedido (POST)
    public function confirmarPedido(): void {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?pagina=login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Error de seguridad. Vuelve a intentarlo.';
            $carrito = $_SESSION['carrito'] ?? [];
            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $direccion = trim($_POST['direccion'] ?? '');

        if ($direccion === '') {
            $error   = 'Por favor, introduce la dirección de envío.';
            $carrito = $_SESSION['carrito'] ?? [];
            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $carrito = $_SESSION['carrito'] ?? [];
        if (empty($carrito)) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        $modeloProducto = new Producto();

        // Verificar stock de todos los productos antes de confirmar
        foreach ($carrito as $item) {
            $producto = $modeloProducto->getPorId($item['producto_id']);
            if (!$producto || $producto['stock'] < $item['cantidad']) {
                $error = "Stock insuficiente para: " . htmlspecialchars($item['nombre']);
                require __DIR__ . '/../views/checkout.php';
                return;
            }
        }

        try {
            $modeloPedido = new Pedido();
            $pedidoId = $modeloPedido->crear(
                $_SESSION['usuario_id'],
                $direccion,
                array_values($carrito)
            );

            // Reducir el stock de cada producto
            foreach ($carrito as $item) {
                $modeloProducto->reducirStock($item['producto_id'], $item['cantidad']);
            }

            // Vaciar el carrito tras el pedido exitoso
            $_SESSION['carrito'] = [];

            // Guardar el ID del pedido para mostrarlo en la confirmación
            $_SESSION['ultimo_pedido_id'] = $pedidoId;

            header('Location: index.php?pagina=confirmacion');
            exit;

        } catch (RuntimeException $e) {
            $error   = 'No se pudo procesar el pedido. Inténtalo de nuevo.';
            $carrito = $_SESSION['carrito'] ?? [];
            require __DIR__ . '/../views/checkout.php';
        }
    }

    // Página: confirmación del pedido
    public function mostrarConfirmacion(): void {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['ultimo_pedido_id'])) {
            header('Location: index.php');
            exit;
        }

        $pedidoId = (int)$_SESSION['ultimo_pedido_id'];
        unset($_SESSION['ultimo_pedido_id']); // Limpiar para evitar recarga accidental

        $modeloPedido = new Pedido();
        $pedido = $modeloPedido->getDetalle($pedidoId, $_SESSION['usuario_id']);

        if (!$pedido) {
            header('Location: index.php');
            exit;
        }

        require __DIR__ . '/../views/confirmacion.php';
    }
}
