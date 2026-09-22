<?php
// views/carrito.php
// Variables disponibles: $carrito (array de items de la sesión)
$tituloPagina = 'Carrito';
require __DIR__ . '/layout/header.php';

// Calcular el total
$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<section class="seccion-carrito">

    <h1 class="titulo-pagina">🛒 Tu carrito</h1>

    <?php if (empty($carrito)): ?>

        <div class="carrito-vacio">
            <p>Tu carrito está vacío.</p>
            <a href="index.php" class="btn btn-primario">Ver catálogo</a>
        </div>

    <?php else: ?>

        <div class="carrito-contenido">

            <!-- Lista de productos -->
            <div class="carrito-lista">
                <?php foreach ($carrito as $item): ?>
                    <div class="carrito-item">
                        <span class="carrito-icono"><?= htmlspecialchars($item['icono']) ?></span>

                        <div class="carrito-item-info">
                            <span class="carrito-item-nombre"><?= htmlspecialchars($item['nombre']) ?></span>
                            <span class="carrito-item-precio"><?= number_format($item['precio'], 2, ',', '.') ?> € / ud.</span>
                        </div>

                        <!-- Actualizar cantidad -->
                        <form action="index.php?accion=actualizar_carrito" method="POST" class="form-cantidad">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="producto_id" value="<?= $item['producto_id'] ?>">
                            <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="0" max="99"
                                   onchange="this.form.submit()" class="input-cantidad">
                        </form>

                        <span class="carrito-item-subtotal">
                            <?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €
                        </span>

                        <!-- Eliminar producto -->
                        <form action="index.php?accion=eliminar_carrito" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="producto_id" value="<?= $item['producto_id'] ?>">
                            <button type="submit" class="btn-eliminar" title="Eliminar">✕</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumen del pedido -->
            <div class="carrito-resumen">
                <h2>Resumen</h2>
                <div class="resumen-linea">
                    <span>Subtotal</span>
                    <span><?= number_format($total, 2, ',', '.') ?> €</span>
                </div>
                <div class="resumen-linea resumen-total">
                    <span>Total</span>
                    <span><?= number_format($total, 2, ',', '.') ?> €</span>
                </div>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="index.php?pagina=checkout" class="btn btn-primario btn-grande btn-block">
                        Finalizar compra
                    </a>
                <?php else: ?>
                    <a href="index.php?pagina=login" class="btn btn-primario btn-grande btn-block">
                        Iniciar sesión para comprar
                    </a>
                <?php endif; ?>

                <!-- Vaciar carrito -->
                <form action="index.php?accion=vaciar_carrito" method="POST" style="margin-top:.5rem;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="btn btn-secundario btn-block">Vaciar carrito</button>
                </form>
            </div>

        </div>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
