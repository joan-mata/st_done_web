<?php
// models/Devolucion.php
// ============================================================
// Modelo: gestiona las devoluciones de pedidos.
// Sin HTML. Solo lógica de acceso a datos con PDO preparado.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Devolucion {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    // -------------------------------------------------------
    // Registrar una nueva devolución para un pedido.
    //
    // Dentro de una transacción:
    //   1. Inserta la fila en la tabla devoluciones.
    //   2. Cambia el estado del pedido a 'devuelto'.
    //
    // Devuelve el ID de la nueva devolución o lanza
    // RuntimeException si ocurre algún error.
    // -------------------------------------------------------
    public function crear(int $pedidoId, string $motivo, string $descripcion): int {
        $this->pdo->beginTransaction();

        try {
            // 1. Insertar el registro de devolución
            $stmtDev = $this->pdo->prepare(
                "INSERT INTO devoluciones (pedido_id, motivo, descripcion)
                 VALUES (:pedido_id, :motivo, :descripcion)
                 RETURNING id"
            );
            $stmtDev->execute([
                ':pedido_id'   => $pedidoId,
                ':motivo'      => $motivo,
                ':descripcion' => $descripcion,
            ]);
            $devolucionId = (int)$stmtDev->fetchColumn();

            // 2. Actualizar el estado del pedido a 'devuelto'
            $stmtPedido = $this->pdo->prepare(
                "UPDATE pedidos SET estado = 'devuelto' WHERE id = :id"
            );
            $stmtPedido->execute([':id' => $pedidoId]);

            $this->pdo->commit();
            return $devolucionId;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Error al crear devolución: ' . $e->getMessage());
            throw new RuntimeException('No se pudo registrar la devolución.');
        }
    }

    // -------------------------------------------------------
    // Obtener la devolución asociada a un pedido.
    // Devuelve el array de la fila o false si no existe
    // ninguna devolución para ese pedido.
    // -------------------------------------------------------
    public function getPorPedido(int $pedidoId): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM devoluciones WHERE pedido_id = :pedido_id"
        );
        $stmt->execute([':pedido_id' => $pedidoId]);
        return $stmt->fetch();
    }
}
