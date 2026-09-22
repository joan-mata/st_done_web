<?php
// views/perfil.php
// Variables: $usuario, $pedidos, $tab ('datos'|'pedidos'), $exito, $error
$tituloPagina = 'Mi cuenta';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-perfil">

    <h1 class="titulo-pagina">Mi cuenta</h1>

    <!-- Pestañas -->
    <div class="perfil-tabs">
        <a href="index.php?pagina=perfil&tab=datos"
           class="perfil-tab <?= $tab === 'datos' ? 'activo' : '' ?>">
            👤 Mi perfil
        </a>
        <a href="index.php?pagina=perfil&tab=pedidos"
           class="perfil-tab <?= $tab === 'pedidos' ? 'activo' : '' ?>">
            📦 Mis pedidos
            <?php if (!empty($pedidos)): ?>
                <span class="tab-badge"><?= count($pedidos) ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- ═══════════════════════════════════════════
         PESTAÑA: MI PERFIL
    ════════════════════════════════════════════════ -->
    <?php if ($tab === 'datos'): ?>

        <div class="perfil-panel">

            <?php if (!empty($exito)): ?>
                <div class="alerta alerta-exito"><?= htmlspecialchars($exito) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="index.php?accion=actualizar_perfil" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <h2 class="perfil-seccion-titulo">Datos personales</h2>

                <div class="form-row">
                    <div class="campo-form">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?= htmlspecialchars($usuario['nombre']) ?>">
                    </div>
                    <div class="campo-form">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($usuario['email']) ?>">
                    </div>
                </div>

                <div class="campo-form" style="max-width:280px;">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="+34 600 000 000"
                           value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                </div>

                <h2 class="perfil-seccion-titulo" style="margin-top:1.5rem;">Dirección guardada
                    <small style="font-size:.8rem;font-weight:400;color:var(--color-texto-suave);">
                        (se pre-rellena en el checkout)
                    </small>
                </h2>

                <div class="campo-form">
                    <label for="direccion">Calle y número</label>
                    <input type="text" id="direccion" name="direccion" placeholder="Calle Mayor, 42"
                           value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="campo-form">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad"
                               value="<?= htmlspecialchars($usuario['ciudad'] ?? '') ?>">
                    </div>
                    <div class="campo-form campo-cp">
                        <label for="codigo_postal">Código postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" maxlength="10"
                               value="<?= htmlspecialchars($usuario['codigo_postal'] ?? '') ?>">
                    </div>
                </div>

                <div class="perfil-acciones">
                    <button type="submit" class="btn btn-primario">Guardar cambios</button>
                    <a href="index.php?accion=logout" class="btn btn-secundario">Cerrar sesión</a>
                </div>
            </form>

            <div class="perfil-meta">
                Miembro desde <?= date('d/m/Y', strtotime($usuario['created_at'])) ?> ·
                Rol: <strong><?= htmlspecialchars($usuario['rol']) ?></strong>
            </div>
        </div>

    <?php endif; ?>

    <!-- ═══════════════════════════════════════════
         PESTAÑA: MIS PEDIDOS
    ════════════════════════════════════════════════ -->
    <?php if ($tab === 'pedidos'): ?>

        <div class="perfil-panel">
            <h2 class="perfil-seccion-titulo">Historial de pedidos</h2>

            <?php if (empty($pedidos)): ?>
                <p class="aviso-vacio">Todavía no has realizado ningún pedido.</p>
                <a href="index.php" class="btn btn-primario">Ver catálogo</a>
            <?php else: ?>
                <div class="lista-pedidos">
                    <?php foreach ($pedidos as $p):
                        $estadoClase = 'estado-' . $p['estado'];
                        $etiqueta = match($p['estado']) {
                            'pendiente'  => '⏳ Pendiente',
                            'procesando' => '⚙️ Preparando',
                            'enviado'    => '🚚 En camino',
                            'entregado'  => '✅ Entregado',
                            'cancelado'  => '❌ Cancelado',
                            'devuelto'   => '↩️ Devuelto',
                            default      => $p['estado'],
                        };
                    ?>
                        <a href="index.php?pagina=pedido&id=<?= $p['id'] ?>" class="pedido-fila">
                            <div class="pedido-fila-izq">
                                <span class="pedido-num">Pedido #<?= $p['id'] ?></span>
                                <span class="pedido-fecha"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
                                <span class="pedido-destinatario">
                                    📦 <?= htmlspecialchars($p['nombre_destinatario'] ?? '') ?>
                                </span>
                            </div>
                            <div class="pedido-fila-der">
                                <span class="pedido-total"><?= number_format($p['total'], 2, ',', '.') ?> €</span>
                                <span class="estado-badge <?= $estadoClase ?>"><?= $etiqueta ?></span>
                                <span class="pedido-ver">Ver detalle →</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
