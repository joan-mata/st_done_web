<?php
// views/confirmacion.php
$cab          = $pedido['cabecera'];
$tituloPagina = 'Pedido confirmado';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-confirmacion">
    <div class="confirmacion-caja">
        <div class="confirmacion-icono">✅</div>
        <h1>¡Pedido confirmado!</h1>
        <p>Tu pedido <strong>#<?= $cab['id'] ?></strong> ha sido registrado correctamente.</p>

        <!-- ETA -->
        <?php if (!empty($cab['fecha_estimada_entrega'])): ?>
            <div class="confirmacion-eta">
                <span class="eta-icono">🚚</span>
                Entrega estimada el <strong><?= date('d/m/Y entre \l\a\s H:i', strtotime($cab['fecha_estimada_entrega'])) ?></strong>
                (~<?= round((strtotime($cab['fecha_estimada_entrega']) - time()) / 3600) ?> horas)
            </div>
        <?php endif; ?>

        <!-- Detalle productos -->
        <div class="detalle-pedido">
            <h2>Resumen del pedido</h2>
            <?php foreach ($pedido['lineas'] as $linea): ?>
                <div class="resumen-linea">
                    <span>
                        <?= htmlspecialchars($linea['icono'] ?? '📦') ?>
                        <?= htmlspecialchars($linea['nombre'] ?? 'Producto') ?>
                        × <?= $linea['cantidad'] ?>
                    </span>
                    <span><?= number_format($linea['precio_unitario'] * $linea['cantidad'], 2, ',', '.') ?> €</span>
                </div>
            <?php endforeach; ?>
            <div class="resumen-linea resumen-total">
                <span>Total</span>
                <span><?= number_format($cab['total'], 2, ',', '.') ?> €</span>
            </div>
        </div>

        <!-- Dirección de envío -->
        <div class="confirmacion-envio">
            <strong>📦 Enviando a:</strong><br>
            <?= htmlspecialchars($cab['nombre_destinatario'] ?? '') ?> —
            <?= htmlspecialchars($cab['calle'] ?? '') ?>,
            <?= htmlspecialchars($cab['ciudad'] ?? '') ?>
            <?= htmlspecialchars($cab['codigo_postal'] ?? '') ?>
        </div>

        <div class="confirmacion-acciones">
            <a href="index.php?pagina=pedido&id=<?= $cab['id'] ?>" class="btn btn-secundario">Ver seguimiento</a>
            <a href="index.php" class="btn btn-primario">Seguir comprando</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
