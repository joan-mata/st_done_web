<?php
// models/Pedido.php
// ============================================================
// Modelo: gestiona pedidos y sus líneas.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Pedido {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    // Crear un pedido completo dentro de una transacción
    // $carrito = [['producto_id'=>X,'nombre'=>..,'precio'=>..,'cantidad'=>..], ...]
    // Devuelve el ID del nuevo pedido o lanza una excepción si algo falla.
    public function crear(int $usuarioId, string $direccion, array $carrito): int {
        // Calcular el total
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        // Usamos una transacción para que si algo falla, no quede nada a medias
        $this->pdo->beginTransaction();

        try {
            // 1. Insertar la cabecera del pedido
            $stmt = $this->pdo->prepare(
                "INSERT INTO pedidos (usuario_id, total, direccion_envio)
                 VALUES (:usuario_id, :total, :direccion)
                 RETURNING id"
            );
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':total'      => $total,
                ':direccion'  => $direccion,
            ]);
            $pedidoId = (int)$stmt->fetchColumn();

            // 2. Insertar cada línea del pedido
            $stmtLinea = $this->pdo->prepare(
                "INSERT INTO lineas_pedido (pedido_id, producto_id, cantidad, precio_unitario)
                 VALUES (:pedido_id, :producto_id, :cantidad, :precio)"
            );
            foreach ($carrito as $item) {
                $stmtLinea->execute([
                    ':pedido_id'  => $pedidoId,
                    ':producto_id'=> $item['producto_id'],
                    ':cantidad'   => $item['cantidad'],
                    ':precio'     => $item['precio'],
                ]);
            }

            $this->pdo->commit();
            return $pedidoId;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Error al crear pedido: ' . $e->getMessage());
            throw new RuntimeException('No se pudo procesar el pedido.');
        }
    }

    // Obtener todos los pedidos de un usuario
    public function getPorUsuario(int $usuarioId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM pedidos WHERE usuario_id = :uid ORDER BY created_at DESC"
        );
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    // Obtener el detalle de un pedido (cabecera + líneas)
    public function getDetalle(int $pedidoId, int $usuarioId): array|false {
        // Verificamos que el pedido pertenece al usuario (seguridad)
        $stmtCab = $this->pdo->prepare(
            "SELECT * FROM pedidos WHERE id = :id AND usuario_id = :uid"
        );
        $stmtCab->execute([':id' => $pedidoId, ':uid' => $usuarioId]);
        $cabecera = $stmtCab->fetch();

        if (!$cabecera) {
            return false; // No existe o no le pertenece
        }

        $stmtLineas = $this->pdo->prepare(
            "SELECT lp.*, p.nombre, p.icono
             FROM lineas_pedido lp
             LEFT JOIN productos p ON p.id = lp.producto_id
             WHERE lp.pedido_id = :pid"
        );
        $stmtLineas->execute([':pid' => $pedidoId]);

        return [
            'cabecera' => $cabecera,
            'lineas'   => $stmtLineas->fetchAll(),
        ];
    }
}
