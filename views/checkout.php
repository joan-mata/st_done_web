<?php
// views/checkout.php
// Variables disponibles: $carrito, $error
$tituloPagina = 'Finalizar compra';
require __DIR__ . '/layout/header.php';

$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<section class="seccion-checkout">

    <h1 class="titulo-pagina">Finalizar compra</h1>

    <?php if (!empty($error)): ?>
        <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="checkout-layout">

        <!-- Formulario de envío -->
        <div class="checkout-form-wrap">
            <h2>Datos de envío</h2>

            <form action="index.php?accion=confirmar_pedido" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="campo-form">
                    <label for="direccion">Dirección de envío</label>
                    <textarea id="direccion" name="direccion" rows="3"
                              required placeholder="Calle, número, piso, ciudad, código postal"></textarea>
                </div>

                <button type="submit" class="btn btn-primario btn-grande btn-block">
                    Confirmar pedido (<?= number_format($total, 2, ',', '.') ?> €)
                </button>
            </form>
        </div>

        <!-- Resumen del pedido -->
        <div class="checkout-resumen">
            <h2>Tu pedido</h2>
            <?php foreach ($carrito as $item): ?>
                <div class="resumen-linea">
                    <span><?= htmlspecialchars($item['icono']) ?> <?= htmlspecialchars($item['nombre']) ?> × <?= $item['cantidad'] ?></span>
                    <span><?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €</span>
                </div>
            <?php endforeach; ?>
            <div class="resumen-linea resumen-total">
                <span>Total</span>
                <span><?= number_format($total, 2, ',', '.') ?> €</span>
            </div>
        </div>

    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
