<?php
// models/Producto.php
// ============================================================
// Modelo: gestiona los datos de productos.
// Solo habla con la BD. No genera HTML.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Producto {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    // Obtener todos los productos activos (con nombre de categoría)
    public function getTodos(?int $categoriaId = null, string $busqueda = ''): array {
        $condiciones = ['p.activo = TRUE'];
        $parametros = [];

        if ($categoriaId !== null) {
            $condiciones[] = 'p.categoria_id = :cat';
            $parametros[':cat'] = $categoriaId;
        }

        if ($busqueda !== '') {
            $condiciones[] = '(p.nombre ILIKE :busqueda OR p.descripcion ILIKE :busqueda OR c.nombre ILIKE :busqueda)';
            $parametros[':busqueda'] = '%' . $busqueda . '%';
        }

        $where = implode(' AND ', $condiciones);
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE $where
             ORDER BY p.nombre"
        );
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    // Obtener un producto por su ID
    public function getPorId(int $id): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.id = :id AND p.activo = TRUE"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Obtener todas las categorías
    public function getCategorias(): array {
        return $this->pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
    }

    // Reducir el stock de un producto al confirmar un pedido
    public function reducirStock(int $productoId, int $cantidad): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE productos
             SET stock = stock - :cantidad
             WHERE id = :id AND stock >= :cantidad"
        );
        $stmt->execute([':cantidad' => $cantidad, ':id' => $productoId]);
        // rowCount() devuelve 0 si el stock era insuficiente
        return $stmt->rowCount() > 0;
    }
}
