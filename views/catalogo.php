<?php
// views/catalogo.php
// Variables disponibles: $productos, $categorias, $categoriaActual
$tituloPagina = 'Catálogo';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-catalogo">

    <h1 class="titulo-pagina">Nuestros productos</h1>

    <!-- Filtro por categoría -->
    <nav class="filtro-categorias">
        <a href="index.php" class="btn-categoria <?= $categoriaActual === null ? 'activo' : '' ?>">
            Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
            <a href="index.php?categoria=<?= $cat['id'] ?>"
               class="btn-categoria <?= $categoriaActual === $cat['id'] ? 'activo' : '' ?>">
                <?= htmlspecialchars($cat['nombre']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Grid de productos -->
    <?php if (empty($productos)): ?>
        <p class="aviso-vacio">No hay productos disponibles en esta categoría.</p>
    <?php else: ?>
        <div class="grid-productos">
            <?php foreach ($productos as $p): ?>
                <article class="tarjeta-producto">
                    <div class="producto-icono"><?= htmlspecialchars($p['icono']) ?></div>
                    <div class="tarjeta-cuerpo">
                        <span class="producto-categoria"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></span>
                        <h2 class="producto-nombre">
                            <a href="index.php?pagina=producto&id=<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nombre']) ?>
                            </a>
                        </h2>
                        <p class="producto-descripcion">
                            <?= htmlspecialchars(mb_substr($p['descripcion'] ?? '', 0, 80)) ?>…
                        </p>
                        <div class="tarjeta-pie">
                            <span class="producto-precio"><?= number_format($p['precio'], 2, ',', '.') ?> €</span>
                            <a href="index.php?pagina=producto&id=<?= $p['id'] ?>" class="btn btn-primario">
                                Ver producto
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
