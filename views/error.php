<?php
// views/error.php
// Variables disponibles: $error (mensaje de error)
$tituloPagina = 'Error';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-error">
    <div class="error-caja">
        <div class="error-icono">⚠️</div>
        <h1>Algo ha salido mal</h1>
        <p><?= htmlspecialchars($error ?? 'Página no encontrada.') ?></p>
        <a href="index.php" class="btn btn-primario">Volver al inicio</a>
    </div>
</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
