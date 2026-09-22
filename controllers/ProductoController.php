<?php
// controllers/ProductoController.php
// ============================================================
// Controlador: gestiona el catálogo y el detalle de producto.
// No contiene HTML ni SQL directos.
// ============================================================

require_once __DIR__ . '/../models/Producto.php';

class ProductoController {

    private Producto $modelo;

    public function __construct() {
        $this->modelo = new Producto();
    }

    // Página: catálogo de productos (con filtro opcional por categoría)
    public function mostrarCatalogo(): void {
        $categoriaId = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;

        $productos   = $this->modelo->getTodos($categoriaId);
        $categorias  = $this->modelo->getCategorias();
        $categoriaActual = $categoriaId;

        require __DIR__ . '/../views/catalogo.php';
    }

    // Página: detalle de un producto
    public function mostrarDetalle(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $producto = $this->modelo->getPorId($id);

        if (!$producto) {
            $error = 'Producto no encontrado.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        require __DIR__ . '/../views/detalle_producto.php';
    }
}
