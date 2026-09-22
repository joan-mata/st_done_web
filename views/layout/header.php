<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'TechShop') ?> — TechShop</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">

        <a href="index.php" class="logo">🛍️ TechShop</a>

        <nav class="nav-principal">
            <a href="index.php" class="nav-link">Catálogo</a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="index.php?pagina=perfil" class="nav-link">Mi cuenta</a>
                <a href="index.php?pagina=logout" class="nav-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="index.php?pagina=login" class="nav-link">Iniciar sesión</a>
                <a href="index.php?pagina=registro" class="nav-link">Registrarse</a>
            <?php endif; ?>
            <a href="index.php?pagina=carrito" class="nav-link carrito-btn">
                🛒 Carrito
                <?php
                $numArticulos = array_sum(array_column($_SESSION['carrito'] ?? [], 'cantidad'));
                if ($numArticulos > 0):
                ?>
                    <span class="carrito-badge"><?= $numArticulos ?></span>
                <?php endif; ?>
            </a>
        </nav>

    </div>
</header>

<main class="contenido-principal">
