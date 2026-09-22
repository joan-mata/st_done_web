<?php
// models/Pedido.php
// ============================================================
// Modelo: gestiona los pedidos y sus líneas de detalle.
// Sin HTML. Solo lógica de acceso a datos con PDO preparado.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Pedido {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    // -------------------------------------------------------
    // Crear un pedido completo dentro de una transacción.
    //
    // $datosEnvio debe contener las claves:
    //   nombre_destinatario, telefono_envio, calle,
    //   ciudad, codigo_postal, pais
    //
    // $carrito es un array de items con las claves:
    //   producto_id, nombre, precio, cantidad
    //
    // Calcula el total sumando precio * cantidad de cada item.
    // Genera la fecha estimada de entrega entre 24 y 36 horas
    // desde ahora usando rand(24,36).
    //
    // Devuelve el ID del nuevo pedido o lanza RuntimeException.
    // -------------------------------------------------------
    public function crear(int $usuarioId, array $datosEnvio, array $carrito): int {
        // Calcular el importe total del pedido
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        // Calcular la fecha estimada de entrega (entre 24 y 36 horas)
        $horasEntrega        = rand(24, 36);
        $fechaEstimada       = date('Y-m-d H:i:s', strtotime("+{$horasEntrega} hours"));

        $this->pdo->beginTransaction();

        try {
            // 1. Insertar la cabecera del pedido
            $stmtPedido = $this->pdo->prepare(
                "INSERT INTO pedidos (
                     usuario_id,
                     total,
                     nombre_destinatario,
                     telefono_envio,
                     calle,
                     ciudad,
                     codigo_postal,
                     pais,
                     fecha_estimada_entrega
                 )
                 VALUES (
                     :usuario_id,
                     :total,
                     :nombre_destinatario,
                     :telefono_envio,
                     :calle,
                     :ciudad,
                     :codigo_postal,
                     :pais,
                     :fecha_estimada_entrega
                 )
                 RETURNING id"
            );
            $stmtPedido->execute([
                ':usuario_id'            => $usuarioId,
                ':total'                 => $total,
                ':nombre_destinatario'   => $datosEnvio['nombre_destinatario'],
                ':telefono_envio'        => $datosEnvio['telefono_envio'],
                ':calle'                 => $datosEnvio['calle'],
                ':ciudad'                => $datosEnvio['ciudad'],
                ':codigo_postal'         => $datosEnvio['codigo_postal'],
                ':pais'                  => $datosEnvio['pais'],
                ':fecha_estimada_entrega' => $fechaEstimada,
            ]);
            $pedidoId = (int)$stmtPedido->fetchColumn();

            // 2. Insertar cada línea del pedido
            $stmtLinea = $this->pdo->prepare(
                "INSERT INTO lineas_pedido (pedido_id, producto_id, cantidad, precio_unitario)
                 VALUES (:pedido_id, :producto_id, :cantidad, :precio)"
            );
            foreach ($carrito as $item) {
                $stmtLinea->execute([
                    ':pedido_id'   => $pedidoId,
                    ':producto_id' => $item['producto_id'],
                    ':cantidad'    => $item['cantidad'],
                    ':precio'      => $item['precio'],
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

    // -------------------------------------------------------
    // Obtener todos los pedidos de un usuario ordenados
    // del más reciente al más antiguo.
    // Devuelve un array (vacío si no tiene pedidos).
    // -------------------------------------------------------
    public function getPorUsuario(int $usuarioId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM pedidos
             WHERE usuario_id = :uid
             ORDER BY created_at DESC"
        );
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Obtener el detalle completo de un pedido.
    // Verifica que el pedido pertenece al usuario indicado
    // antes de devolver datos (control de acceso).
    // Devuelve ['cabecera' => fila, 'lineas' => array] o false.
    // -------------------------------------------------------
    public function getDetalle(int $pedidoId, int $usuarioId): array|false {
        // Comprobar que el pedido existe y pertenece al usuario
        $stmtCab = $this->pdo->prepare(
            "SELECT * FROM pedidos WHERE id = :id AND usuario_id = :uid"
        );
        $stmtCab->execute([':id' => $pedidoId, ':uid' => $usuarioId]);
        $cabecera = $stmtCab->fetch();

        if (!$cabecera) {
            // El pedido no existe o no pertenece a este usuario
            return false;
        }

        // Obtener las líneas con nombre e icono del producto
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

    // -------------------------------------------------------
    // Actualizar automáticamente el estado de un pedido
    // según el tiempo transcurrido desde su creación y la
    // fecha estimada de entrega.
    //
    // Lógica de estados:
    //   - 'pendiente'  : ahora < created_at + 1 hora
    //   - 'procesando' : ahora < created_at + 4 horas
    //   - 'enviado'    : ahora < fecha_estimada_entrega
    //   - 'entregado'  : ahora >= fecha_estimada_entrega
    //
    // No modifica pedidos en estado 'cancelado' o 'devuelto'.
    // Devuelve el estado actual (tras la posible actualización).
    // -------------------------------------------------------
    public function actualizarEstadoAutomatico(int $pedidoId): string {
        // Cargar solo los campos necesarios para el cálculo
        $stmt = $this->pdo->prepare(
            "SELECT id, estado, created_at, fecha_estimada_entrega
             FROM pedidos
             WHERE id = :id"
        );
        $stmt->execute([':id' => $pedidoId]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            return 'desconocido';
        }

        $ahora          = time();
        $createdAt      = strtotime($pedido['created_at']);
        $fechaEstimada  = $pedido['fecha_estimada_entrega']
                            ? strtotime($pedido['fecha_estimada_entrega'])
                            : null;

        // Calcular el estado que debería tener ahora
        if ($ahora < $createdAt + 3600) {
            // Menos de 1 hora desde la creación
            $estadoCalculado = 'pendiente';
        } elseif ($ahora < $createdAt + 14400) {
            // Menos de 4 horas desde la creación
            $estadoCalculado = 'procesando';
        } elseif ($fechaEstimada !== null && $ahora < $fechaEstimada) {
            // Antes de la fecha estimada de entrega
            $estadoCalculado = 'enviado';
        } else {
            // Ha llegado o superado la fecha estimada
            $estadoCalculado = 'entregado';
        }

        // Estados protegidos que no deben actualizarse automáticamente
        $estadosProtegidos = ['cancelado', 'devuelto'];

        // Actualizar solo si el estado ha cambiado y no está protegido
        if (
            $estadoCalculado !== $pedido['estado'] &&
            !in_array($pedido['estado'], $estadosProtegidos, true)
        ) {
            $stmtUpdate = $this->pdo->prepare(
                "UPDATE pedidos SET estado = :estado WHERE id = :id"
            );
            $stmtUpdate->execute([
                ':estado' => $estadoCalculado,
                ':id'     => $pedidoId,
            ]);
            return $estadoCalculado;
        }

        // Devolver el estado actual (no ha cambiado o está protegido)
        return $pedido['estado'];
    }

    // -------------------------------------------------------
    // Obtener un pedido por su ID sin verificar el usuario.
    // Para uso interno del modelo (no exponer directamente
    // a rutas públicas sin comprobación previa de permisos).
    // Devuelve el array de la fila o false si no existe.
    // -------------------------------------------------------
    public function getPorId(int $pedidoId): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM pedidos WHERE id = :id"
        );
        $stmt->execute([':id' => $pedidoId]);
        return $stmt->fetch();
    }

    // -------------------------------------------------------
    // Comprobar si un pedido es elegible para devolución.
    // Condiciones:
    //   1. El estado es 'entregado'.
    //   2. La fecha estimada de entrega no es nula.
    //   3. Han pasado menos de 3 días desde la entrega estimada.
    //
    // Recibe el array de cabecera del pedido (ya cargado).
    // Devuelve true si se puede devolver, false en caso contrario.
    // -------------------------------------------------------
    public function puedeDevolver(array $cabeceraPedido): bool {
        // Solo se pueden devolver pedidos en estado 'entregado'
        if ($cabeceraPedido['estado'] !== 'entregado') {
            return false;
        }

        // Debe existir la fecha estimada de entrega
        if (empty($cabeceraPedido['fecha_estimada_entrega'])) {
            return false;
        }

        $ahora          = time();
        $fechaEntrega   = strtotime($cabeceraPedido['fecha_estimada_entrega']);
        $tresDiasSegundos = 3 * 24 * 3600; // 259200 segundos

        // El plazo de devolución no debe haber expirado (menos de 3 días)
        return ($ahora - $fechaEntrega) < $tresDiasSegundos;
    }
}
