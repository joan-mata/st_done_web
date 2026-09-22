<?php
// views/login.php
// Variables disponibles: $error (string, puede estar vacío)
$tituloPagina = 'Iniciar sesión';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-auth">

    <div class="tarjeta-auth">
        <h1 class="titulo-pagina">Iniciar sesión</h1>

        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php?accion=login" method="POST" novalidate>
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="campo-form">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       required autocomplete="email" placeholder="tu@email.com">
            </div>

            <div class="campo-form">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password"
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primario btn-block">Entrar</button>
        </form>

        <p class="auth-enlace">
            ¿No tienes cuenta?
            <a href="index.php?pagina=registro">Regístrate aquí</a>
        </p>
    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
