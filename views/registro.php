<?php
// views/registro.php
// Variables disponibles: $error (string, puede estar vacío)
$tituloPagina = 'Registro';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-auth">

    <div class="tarjeta-auth">
        <h1 class="titulo-pagina">Crear cuenta</h1>

        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php?accion=registro" method="POST" novalidate>
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="campo-form">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre"
                       required autocomplete="name" placeholder="Tu nombre">
            </div>

            <div class="campo-form">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       required autocomplete="email" placeholder="tu@email.com">
            </div>

            <div class="campo-form">
                <label for="password">Contraseña <small>(mínimo 8 caracteres)</small></label>
                <input type="password" id="password" name="password"
                       required minlength="8" autocomplete="new-password">
            </div>

            <div class="campo-form">
                <label for="password2">Repite la contraseña</label>
                <input type="password" id="password2" name="password2"
                       required minlength="8" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primario btn-block">Crear cuenta</button>
        </form>

        <p class="auth-enlace">
            ¿Ya tienes cuenta?
            <a href="index.php?pagina=login">Inicia sesión</a>
        </p>
    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
