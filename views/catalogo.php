<?php
// views/catalogo.php
// Variables disponibles: $productos, $categorias, $categoriaActual, $busqueda
$tituloPagina = 'Catálogo';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-catalogo">

    <h1 class="titulo-pagina">Nuestros productos</h1>

    <form class="buscador-productos" action="index.php" method="GET" role="search">
        <input type="hidden" name="pagina" value="catalogo">
        <?php if ($categoriaActual !== null): ?>
            <input type="hidden" name="categoria" value="<?= $categoriaActual ?>">
        <?php endif; ?>
        <label class="sr-only" for="busqueda-productos">Buscar productos</label>
        <input id="busqueda-productos" type="search" name="q"
               value="<?= htmlspecialchars($busqueda) ?>"
               placeholder="Buscar por nombre, categoría o descripción"
               autocomplete="off">
        <button type="submit" class="btn btn-primario">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a href="index.php<?= $categoriaActual !== null ? '?categoria=' . $categoriaActual : '' ?>"
               class="btn btn-secundario">Limpiar</a>
        <?php endif; ?>
    </form>

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
        <p class="aviso-vacio">
            <?= $busqueda !== ''
                ? 'No encontramos productos para «' . htmlspecialchars($busqueda) . '».'
                : 'No hay productos disponibles en esta categoría.' ?>
        </p>
    <?php else: ?>
        <div class="grid-productos">
            <?php foreach ($productos as $producto): ?>
                <?php $textoBusqueda = implode(' ', [
                    $producto['nombre'],
                    $producto['categoria_nombre'] ?? '',
                    $producto['descripcion'] ?? '',
                ]); ?>
                <article class="tarjeta-producto"
                         data-producto-busqueda="<?= htmlspecialchars($textoBusqueda) ?>">
                    <div class="producto-imagen">
                        <img src="<?= htmlspecialchars($producto['imagen_url']) ?>"
                             alt="<?= htmlspecialchars($producto['nombre']) ?>"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="producto-icono-fallback" style="display:none"><?= htmlspecialchars($producto['icono']) ?></div>
                    </div>
                    <div class="tarjeta-cuerpo">
                        <span class="producto-categoria"><?= htmlspecialchars($producto['categoria_nombre'] ?? '') ?></span>
                        <h2 class="producto-nombre">
                            <a href="index.php?pagina=producto&id=<?= $producto['id'] ?>">
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </a>
                        </h2>
                        <p class="producto-descripcion">
                            <?= htmlspecialchars(mb_substr($producto['descripcion'] ?? '', 0, 80)) ?>…
                        </p>
                        <div class="tarjeta-pie">
                            <span class="producto-precio"><?= number_format($producto['precio'], 2, ',', '.') ?> €</span>
                            <a href="index.php?pagina=producto&id=<?= $producto['id'] ?>" class="btn btn-primario">
                                Ver producto
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p id="buscador-sin-resultados" class="aviso-vacio buscador-sin-resultados" hidden>
            No encontramos productos con ese texto.
        </p>
    <?php endif; ?>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
