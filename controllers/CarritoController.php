<?php
// controllers/CarritoController.php
// ============================================================
// Controlador: gestiona el carrito de la compra (en sesión).
// El carrito se guarda en $_SESSION['carrito'] para no necesitar
// una tabla extra en la BD mientras el usuario navega.
// ============================================================

require_once __DIR__ . '/../models/Producto.php';

class CarritoController {

    // Página: ver el carrito
    public function mostrarCarrito(): void {
        $carrito = $_SESSION['carrito'] ?? [];
        require __DIR__ . '/../views/carrito.php';
    }

    // Acción: añadir un producto al carrito (POST)
    public function agregarProducto(): void {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: index.php');
            exit;
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $cantidad   = (int)($_POST['cantidad']    ?? 1);

        if ($productoId <= 0 || $cantidad <= 0) {
            header('Location: index.php');
            exit;
        }

        // Verificar que el producto existe y tiene stock
        $modeloProducto = new Producto();
        $producto = $modeloProducto->getPorId($productoId);

        if (!$producto) {
            header('Location: index.php');
            exit;
        }

        // Inicializar el carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // Si el producto ya está en el carrito, sumamos la cantidad
        if (isset($_SESSION['carrito'][$productoId])) {
            $nuevaCantidad = $_SESSION['carrito'][$productoId]['cantidad'] + $cantidad;
            // No permitir añadir más unidades de las que hay en stock
            $_SESSION['carrito'][$productoId]['cantidad'] = min($nuevaCantidad, $producto['stock']);
        } else {
            $_SESSION['carrito'][$productoId] = [
                'producto_id' => $producto['id'],
                'nombre'      => $producto['nombre'],
                'precio'      => $producto['precio'],
                'icono'       => $producto['icono'],
                'cantidad'    => min($cantidad, $producto['stock']),
            ];
        }

        header('Location: index.php?pagina=carrito');
        exit;
    }

    // Acción: actualizar cantidad de un producto en el carrito (POST)
    public function actualizarCantidad(): void {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $cantidad   = (int)($_POST['cantidad']    ?? 0);

        if ($cantidad <= 0) {
            // Si la cantidad es 0 o negativa, eliminar el producto
            unset($_SESSION['carrito'][$productoId]);
        } elseif (isset($_SESSION['carrito'][$productoId])) {
            $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;
        }

        header('Location: index.php?pagina=carrito');
        exit;
    }

    // Acción: eliminar un producto del carrito
    public function eliminarProducto(): void {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        unset($_SESSION['carrito'][$productoId]);

        header('Location: index.php?pagina=carrito');
        exit;
    }

    // Acción: vaciar el carrito
    public function vaciarCarrito(): void {
        $_SESSION['carrito'] = [];
        header('Location: index.php?pagina=carrito');
        exit;
    }
}
