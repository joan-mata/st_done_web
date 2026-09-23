<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'TechShop') ?> — TechShop</title>
    <link rel="stylesheet" href="css/estilos.css">
    <?php if (!empty($cssExtra)): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($cssExtra) ?>">
    <?php endif; ?>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo">🛍️ TechShop</a>

        <a href="index.php?pagina=explorador_db" class="btn-explorar-db" aria-label="Explorador educativo de base de datos">
            <span aria-hidden="true">◉</span> DB Explorer
        </a>

        <nav class="nav-principal">
            <a href="index.php" class="nav-link">Catálogo</a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="index.php?pagina=perfil" class="nav-link">Mi cuenta</a>
                <a href="index.php?accion=logout" class="nav-link">Salir</a>
            <?php else: ?>
                <a href="index.php?pagina=login" class="nav-link">Iniciar sesión</a>
                <a href="index.php?pagina=registro" class="nav-link">Registrarse</a>
            <?php endif; ?>

            <!-- Botón carrito → abre el popup -->
            <button class="carrito-btn" id="btn-abrir-carrito" aria-label="Abrir carrito">
                <span aria-hidden="true">🛒</span> Carrito
                <?php
                $numArticulos = array_sum(array_column($_SESSION['carrito'] ?? [], 'cantidad'));
                if ($numArticulos > 0): ?>
                    <span class="carrito-badge" id="carrito-badge"><?= $numArticulos ?></span>
                <?php endif; ?>
            </button>
        </nav>
    </div>
</header>

<!-- ═══════════════════════════════════════════════
     POPUP / DRAWER DEL CARRITO
═════════════════════════════════════════════════ -->
<div class="carrito-overlay" id="carrito-overlay"></div>

<aside class="carrito-drawer" id="carrito-drawer" aria-hidden="true">
    <div class="drawer-header">
        <h2>🛒 Tu carrito</h2>
        <button class="drawer-cerrar" id="btn-cerrar-carrito" aria-label="Cerrar">✕</button>
    </div>

    <div class="drawer-cuerpo">
        <?php
        $carritoSesion = $_SESSION['carrito'] ?? [];
        $totalDrawer   = 0;
        foreach ($carritoSesion as $item) {
            $totalDrawer += $item['precio'] * $item['cantidad'];
        }
        ?>

        <?php if (empty($carritoSesion)): ?>
            <p class="drawer-vacio">Tu carrito está vacío.</p>
        <?php else: ?>
            <ul class="drawer-lista">
                <?php foreach ($carritoSesion as $item): ?>
                    <li class="drawer-item">
                        <span class="drawer-icono"><?= htmlspecialchars($item['icono']) ?></span>
                        <div class="drawer-item-info">
                            <span class="drawer-item-nombre"><?= htmlspecialchars($item['nombre']) ?></span>
                            <span class="drawer-item-precio">
                                <?= $item['cantidad'] ?> × <?= number_format($item['precio'], 2, ',', '.') ?> €
                            </span>
                        </div>
                        <span class="drawer-item-sub">
                            <?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="drawer-total">
                <span>Total</span>
                <span><?= number_format($totalDrawer, 2, ',', '.') ?> €</span>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($carritoSesion)): ?>
    <div class="drawer-pie">
        <a href="index.php?pagina=carrito" class="btn btn-secundario btn-block">
            Ver carrito completo
        </a>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="index.php?pagina=checkout" class="btn btn-primario btn-block">
                Pagar ahora — <?= number_format($totalDrawer, 2, ',', '.') ?> €
            </a>
        <?php else: ?>
            <a href="index.php?pagina=login" class="btn btn-primario btn-block">
                Iniciar sesión para pagar
            </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="drawer-pie">
        <a href="index.php" class="btn btn-primario btn-block">Ver catálogo</a>
    </div>
    <?php endif; ?>
</aside>

<main class="contenido-principal">
