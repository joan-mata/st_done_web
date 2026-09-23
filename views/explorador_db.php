<?php
// views/explorador_db.php
// Variables: $tablas, $tablaActual, $resumen, $filas
$tituloPagina = 'Explorar la base de datos';
$definicion = $tablas[$tablaActual];
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-db">
    <div class="db-hero">
        <span class="db-kicker">Zona educativa</span>
        <h1 class="titulo-pagina">Así se estructura TechShop</h1>
        <p>Explora las tablas que conectan el catálogo, los pedidos y el seguimiento. Esta vista es solo de lectura.</p>
    </div>

    <div class="db-layout">
        <aside class="db-sidebar">
            <h2>Tablas</h2>
            <nav aria-label="Tablas de la base de datos">
                <?php foreach ($tablas as $clave => $tabla): ?>
                    <a class="db-tabla-enlace <?= $tablaActual === $clave ? 'activo' : '' ?>"
                       href="index.php?pagina=explorador_db&tabla=<?= urlencode($clave) ?>">
                        <span><?= htmlspecialchars($tabla['nombre']) ?></span>
                        <strong><?= $resumen[$clave] ?></strong>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="db-contenido">
            <section class="db-diagrama">
                <div class="db-seccion-cabecera">
                    <div>
                        <span class="db-kicker">Mapa de relaciones</span>
                        <h2>Del producto al pedido</h2>
                    </div>
                    <span class="db-lectura">Solo lectura</span>
                </div>
                <div class="db-relaciones" aria-label="Relaciones entre tablas">
                    <div class="db-entidad db-entidad-principal"><strong>categorias</strong><span>1</span></div>
                    <div class="db-relacion-linea"><span>tiene muchos</span><b>N</b></div>
                    <div class="db-entidad"><strong>productos</strong><span>categoria_id → categorias.id</span></div>
                    <div class="db-relacion-linea"><span>aparece en</span><b>N</b></div>
                    <div class="db-entidad"><strong>lineas_pedido</strong><span>pedido_id · producto_id</span></div>
                    <div class="db-relacion-linea"><span>pertenece a</span><b>1</b></div>
                    <div class="db-entidad db-entidad-principal"><strong>pedidos</strong><span>usuario_id → usuarios.id</span></div>
                </div>
                <p class="db-nota">Las relaciones usan claves foráneas: conectan filas sin duplicar toda la información.</p>
            </section>

            <section class="db-tabla-panel">
                <div class="db-seccion-cabecera">
                    <div>
                        <span class="db-kicker">Tabla seleccionada</span>
                        <h2><?= htmlspecialchars($definicion['nombre']) ?></h2>
                        <p><?= htmlspecialchars($definicion['descripcion']) ?></p>
                    </div>
                    <code><?= htmlspecialchars($tablaActual) ?></code>
                </div>

                <div class="db-columnas">
                    <?php foreach ($definicion['columnas'] as $columna): ?>
                        <code><?= htmlspecialchars($columna) ?></code>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($filas)): ?>
                    <p class="aviso-vacio">Esta tabla todavía no tiene filas.</p>
                <?php else: ?>
                    <div class="db-tabla-scroll">
                        <table class="db-datos">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($filas[0]) as $columna): ?>
                                        <th><?= htmlspecialchars($columna) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filas as $fila): ?>
                                    <tr>
                                        <?php foreach ($fila as $valor): ?>
                                            <td><?= htmlspecialchars((string)($valor ?? 'NULL')) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="db-pie-tabla">Mostrando hasta 50 filas · Los campos privados no se muestran en esta vista.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
