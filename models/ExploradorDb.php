<?php
// models/ExploradorDb.php
// Consultas educativas de solo lectura. No acepta SQL proporcionado por el usuario.

require_once __DIR__ . '/../config/database.php';

class ExploradorDb {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    public function getTablas(): array {
        return [
            'categorias' => [
                'nombre' => 'Categorías',
                'descripcion' => 'Agrupa los productos del catálogo.',
                'columnas' => ['id', 'nombre'],
            ],
            'productos' => [
                'nombre' => 'Productos',
                'descripcion' => 'Catálogo, precios, stock e imágenes.',
                'columnas' => ['id', 'nombre', 'precio', 'stock', 'categoria_id', 'activo'],
            ],
            'usuarios' => [
                'nombre' => 'Usuarios',
                'descripcion' => 'Clientes y administradores. La contraseña se almacena como hash bcrypt, nunca en texto plano.',
                'columnas' => ['id', 'nombre', 'email', 'password_hash', 'rol', 'created_at'],
            ],
            'pedidos' => [
                'nombre' => 'Pedidos',
                'descripcion' => 'Cabecera de las compras y su estado de seguimiento.',
                'columnas' => ['id', 'usuario_id', 'total', 'estado', 'created_at'],
            ],
            'lineas_pedido' => [
                'nombre' => 'Líneas de pedido',
                'descripcion' => 'Productos y cantidades que componen cada pedido.',
                'columnas' => ['id', 'pedido_id', 'producto_id', 'cantidad', 'precio_unitario'],
            ],
            'devoluciones' => [
                'nombre' => 'Devoluciones',
                'descripcion' => 'Solicitudes de devolución asociadas a pedidos.',
                'columnas' => ['id', 'pedido_id', 'motivo', 'estado', 'created_at'],
            ],
        ];
    }

    public function getResumen(): array {
        $tablas = array_keys($this->getTablas());
        $resumen = [];

        foreach ($tablas as $tabla) {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM $tabla");
            $resumen[$tabla] = (int)$stmt->fetchColumn();
        }

        return $resumen;
    }

    public function getFilas(string $tabla): array {
        $consultas = [
            'categorias' => "SELECT id, nombre FROM categorias ORDER BY id LIMIT 50",
            'productos' => "SELECT id, nombre, precio, stock, categoria_id, activo FROM productos ORDER BY id LIMIT 50",
            'usuarios' => "SELECT id, nombre, email, concat(substr(password_hash,1,8), '....', right(password_hash,4)) AS password_hash, rol, created_at FROM usuarios ORDER BY id LIMIT 50",
            'pedidos' => "SELECT id, usuario_id, total, estado, created_at FROM pedidos ORDER BY id DESC LIMIT 50",
            'lineas_pedido' => "SELECT id, pedido_id, producto_id, cantidad, precio_unitario FROM lineas_pedido ORDER BY id DESC LIMIT 50",
            'devoluciones' => "SELECT id, pedido_id, motivo, estado, created_at FROM devoluciones ORDER BY id DESC LIMIT 50",
        ];

        if (!isset($consultas[$tabla])) {
            return [];
        }

        return $this->pdo->query($consultas[$tabla])->fetchAll();
    }
}
