<?php
// views/confirmacion.php
// Variables disponibles: $pedido (array con 'cabecera' y 'lineas')
$tituloPagina = 'Pedido confirmado';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-confirmacion">

    <div class="confirmacion-caja">
        <div class="confirmacion-icono">✅</div>
        <h1>¡Pedido confirmado!</h1>
        <p>Tu pedido <strong>#<?= $pedido['cabecera']['id'] ?></strong> ha sido registrado correctamente.</p>

        <div class="detalle-pedido">
            <h2>Detalle del pedido</h2>
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
                <span>Total pagado</span>
                <span><?= number_format($pedido['cabecera']['total'], 2, ',', '.') ?> €</span>
            </div>
        </div>

        <p class="direccion-envio">
            📦 Dirección de envío: <em><?= htmlspecialchars($pedido['cabecera']['direccion_envio']) ?></em>
        </p>

        <div class="confirmacion-acciones">
            <a href="index.php?pagina=perfil" class="btn btn-secundario">Ver mis pedidos</a>
            <a href="index.php" class="btn btn-primario">Seguir comprando</a>
        </div>
    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
