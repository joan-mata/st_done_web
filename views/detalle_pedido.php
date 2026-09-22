<?php
// views/detalle_pedido.php
// Variables: $pedido (['cabecera', 'lineas']), $puedeDevolver, $devolucion
$cab          = $pedido['cabecera'];
$tituloPagina = 'Pedido #' . $cab['id'];
require __DIR__ . '/layout/header.php';

// ── Calcular progreso del seguimiento ────────────────────────
$estado        = $cab['estado'];
$createdAt     = strtotime($cab['created_at']);
$fechaEstimada = $cab['fecha_estimada_entrega'] ? strtotime($cab['fecha_estimada_entrega']) : null;
$ahora         = time();

// Porcentaje de progreso visual (0-100)
$pasos = ['pendiente' => 1, 'procesando' => 2, 'enviado' => 3, 'entregado' => 4];
$pasoActual = $pasos[$estado] ?? ($estado === 'cancelado' || $estado === 'devuelto' ? 0 : 4);

// Horas restantes hasta la entrega estimada
$horasRestantes = null;
if ($fechaEstimada && $estado === 'enviado') {
    $segundosRestantes = $fechaEstimada - $ahora;
    $horasRestantes = max(0, round($segundosRestantes / 3600, 1));
}
?>

<section class="seccion-detalle-pedido">

    <a href="index.php?pagina=perfil&tab=pedidos" class="enlace-volver">← Volver a mis pedidos</a>

    <div class="pedido-detalle-cabecera">
        <div>
            <h1 class="titulo-pagina" style="margin-bottom:.2rem;">Pedido #<?= $cab['id'] ?></h1>
            <p class="pedido-detalle-fecha">Realizado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($cab['created_at'])) ?></p>
        </div>
        <?php
        $etiqueta = match($estado) {
            'pendiente'  => '⏳ Pendiente',
            'procesando' => '⚙️ En preparación',
            'enviado'    => '🚚 En camino',
            'entregado'  => '✅ Entregado',
            'cancelado'  => '❌ Cancelado',
            'devuelto'   => '↩️ Devuelto',
            default      => $estado,
        };
        ?>
        <span class="estado-badge estado-<?= $estado ?> estado-grande"><?= $etiqueta ?></span>
    </div>

    <!-- ═══ SEGUIMIENTO ═════════════════════════════════════ -->
    <?php if (!in_array($estado, ['cancelado', 'devuelto'])): ?>
    <div class="seguimiento-caja">
        <h2>Seguimiento del envío</h2>

        <!-- Barra de progreso de 4 pasos -->
        <div class="seguimiento-pasos">
            <?php
            $definicionPasos = [
                ['clave' => 'pendiente',  'icono' => '📋', 'etiqueta' => 'Confirmado',   'num' => 1],
                ['clave' => 'procesando', 'icono' => '⚙️', 'etiqueta' => 'Preparando',   'num' => 2],
                ['clave' => 'enviado',    'icono' => '🚚', 'etiqueta' => 'En camino',     'num' => 3],
                ['clave' => 'entregado',  'icono' => '✅', 'etiqueta' => 'Entregado',     'num' => 4],
            ];
            foreach ($definicionPasos as $i => $paso):
                $completado = $pasoActual >= $paso['num'];
                $actual     = $pasoActual === $paso['num'];
            ?>
                <?php if ($i > 0): ?>
                    <div class="seguimiento-linea <?= $pasoActual > $paso['num'] ? 'completada' : '' ?>"></div>
                <?php endif; ?>
                <div class="seguimiento-paso <?= $completado ? 'completado' : '' ?> <?= $actual ? 'actual' : '' ?>">
                    <div class="paso-circulo"><?= $completado ? $paso['icono'] : $paso['num'] ?></div>
                    <div class="paso-etiqueta"><?= $paso['etiqueta'] ?></div>
                    <?php if ($paso['clave'] === 'pendiente'): ?>
                        <div class="paso-hora"><?= date('d/m H:i', $createdAt) ?></div>
                    <?php elseif ($paso['clave'] === 'procesando' && $pasoActual >= 2): ?>
                        <div class="paso-hora"><?= date('d/m H:i', $createdAt + 3600) ?></div>
                    <?php elseif ($paso['clave'] === 'enviado' && $pasoActual >= 3): ?>
                        <div class="paso-hora"><?= date('d/m H:i', $createdAt + 4*3600) ?></div>
                    <?php elseif ($paso['clave'] === 'entregado' && $fechaEstimada): ?>
                        <div class="paso-hora"><?= date('d/m H:i', $fechaEstimada) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ETA -->
        <?php if ($horasRestantes !== null && $estado === 'enviado'): ?>
            <div class="seguimiento-eta">
                <span class="eta-icono">⏱</span>
                <span>
                    <?php if ($horasRestantes < 1): ?>
                        Llegará en menos de 1 hora
                    <?php else: ?>
                        Llegará en aproximadamente <strong><?= $horasRestantes ?> horas</strong>
                        (<?= date('d/m a \l\a\s H:i', $fechaEstimada) ?>)
                    <?php endif; ?>
                </span>
            </div>
        <?php elseif ($estado === 'entregado' && $fechaEstimada): ?>
            <div class="seguimiento-eta eta-entregado">
                ✅ Entregado el <?= date('d/m/Y a \l\a\s H:i', $fechaEstimada) ?>
            </div>
        <?php elseif ($fechaEstimada): ?>
            <div class="seguimiento-eta">
                Entrega estimada: <?= date('d/m/Y H:i', $fechaEstimada) ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="pedido-detalle-layout">

        <!-- ═══ PRODUCTOS DEL PEDIDO ════════════════════════ -->
        <div class="pedido-detalle-productos">
            <h2>Productos</h2>
            <?php foreach ($pedido['lineas'] as $linea): ?>
                <div class="linea-pedido">
                    <span class="linea-icono"><?= htmlspecialchars($linea['icono'] ?? '📦') ?></span>
                    <div class="linea-info">
                        <span class="linea-nombre"><?= htmlspecialchars($linea['nombre'] ?? 'Producto eliminado') ?></span>
                        <span class="linea-cant"><?= $linea['cantidad'] ?> ud. × <?= number_format($linea['precio_unitario'], 2, ',', '.') ?> €</span>
                    </div>
                    <span class="linea-sub">
                        <?= number_format($linea['precio_unitario'] * $linea['cantidad'], 2, ',', '.') ?> €
                    </span>
                </div>
            <?php endforeach; ?>
            <div class="resumen-linea resumen-total">
                <span>Total</span>
                <span><?= number_format($cab['total'], 2, ',', '.') ?> €</span>
            </div>
        </div>

        <!-- ═══ DIRECCIÓN DE ENVÍO ═══════════════════════════ -->
        <div class="pedido-detalle-envio">
            <h2>Envío</h2>
            <address class="envio-address">
                <strong><?= htmlspecialchars($cab['nombre_destinatario'] ?? '') ?></strong><br>
                <?php if (!empty($cab['telefono_envio'])): ?>
                    📞 <?= htmlspecialchars($cab['telefono_envio']) ?><br>
                <?php endif; ?>
                <?= htmlspecialchars($cab['calle'] ?? '') ?><br>
                <?= htmlspecialchars($cab['codigo_postal'] ?? '') ?> <?= htmlspecialchars($cab['ciudad'] ?? '') ?><br>
                <?= htmlspecialchars($cab['pais'] ?? 'España') ?>
            </address>
        </div>

    </div>

    <!-- ═══ DEVOLUCIÓN ══════════════════════════════════════ -->
    <?php if ($devolucion): ?>
        <div class="devolucion-info alerta alerta-aviso">
            <strong>↩️ Devolución solicitada</strong> —
            Motivo: <?= htmlspecialchars($devolucion['motivo']) ?> ·
            Estado: <strong><?= htmlspecialchars($devolucion['estado']) ?></strong>
        </div>
    <?php elseif ($puedeDevolver): ?>
        <div class="devolucion-cta">
            <p>¿Algo no va bien? Tienes hasta 3 días desde la entrega para devolver tu pedido.</p>
            <a href="index.php?pagina=devolucion&pedido_id=<?= $cab['id'] ?>" class="btn btn-secundario">
                ↩️ Solicitar devolución
            </a>
        </div>
    <?php endif; ?>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
