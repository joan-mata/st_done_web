<?php
// views/perfil.php
// Variables disponibles: $usuario, $pedidos
$tituloPagina = 'Mi cuenta';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-perfil">

    <h1 class="titulo-pagina">Mi cuenta</h1>

    <!-- Datos del usuario -->
    <div class="perfil-tarjeta">
        <h2>👤 Tus datos</h2>
        <table class="tabla-datos">
            <tr>
                <th>Nombre</th>
                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
            </tr>
            <tr>
                <th>Rol</th>
                <td><?= htmlspecialchars($usuario['rol']) ?></td>
            </tr>
            <tr>
                <th>Registrado el</th>
                <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
            </tr>
        </table>
        <a href="index.php?pagina=logout" class="btn btn-secundario" style="margin-top:1rem;">
            Cerrar sesión
        </a>
    </div>

    <!-- Historial de pedidos -->
    <div class="perfil-pedidos">
        <h2>📦 Mis pedidos</h2>

        <?php if (empty($pedidos)): ?>
            <p class="aviso-vacio">Todavía no has realizado ningún pedido.</p>
            <a href="index.php" class="btn btn-primario">Ver catálogo</a>
        <?php else: ?>
            <table class="tabla-pedidos">
                <thead>
                    <tr>
                        <th>Pedido #</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= $pedido['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
                            <td><?= number_format($pedido['total'], 2, ',', '.') ?> €</td>
                            <td>
                                <span class="estado-badge estado-<?= $pedido['estado'] ?>">
                                    <?= htmlspecialchars($pedido['estado']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
