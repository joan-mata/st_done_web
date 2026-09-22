<?php
// views/detalle_producto.php
// Variables disponibles: $producto
$tituloPagina = htmlspecialchars($producto['nombre']);
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-detalle">

    <a href="index.php" class="enlace-volver">← Volver al catálogo</a>

    <div class="detalle-producto">

        <!-- Imagen del producto -->
        <div class="detalle-imagen">
            <img src="<?= htmlspecialchars($producto['imagen_url']) ?>"
                 alt="<?= htmlspecialchars($producto['nombre']) ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="detalle-icono-fallback" style="display:none"><?= htmlspecialchars($producto['icono']) ?></div>
        </div>

        <!-- Información del producto -->
        <div class="detalle-info">
            <span class="producto-categoria"><?= htmlspecialchars($producto['categoria_nombre'] ?? '') ?></span>
            <h1 class="detalle-nombre"><?= htmlspecialchars($producto['nombre']) ?></h1>
            <p class="detalle-descripcion"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></p>

            <div class="detalle-precio"><?= number_format($producto['precio'], 2, ',', '.') ?> €</div>

            <?php if ($producto['stock'] > 0): ?>
                <p class="stock-disponible">✓ En stock (<?= $producto['stock'] ?> unidades)</p>

                <!-- Formulario: añadir al carrito -->
                <form action="index.php?accion=agregar_carrito" method="POST" class="form-agregar">
                    <!-- Token CSRF para evitar ataques CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">

                    <div class="campo-cantidad">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad"
                               value="1" min="1" max="<?= $producto['stock'] ?>">
                    </div>

                    <button type="submit" class="btn btn-primario btn-grande">
                        🛒 Añadir al carrito
                    </button>
                </form>

            <?php else: ?>
                <p class="stock-agotado">✗ Sin stock</p>
            <?php endif; ?>
        </div>

    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
