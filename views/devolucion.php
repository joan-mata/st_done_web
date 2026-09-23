<?php
// views/devolucion.php
// Variables: $pedido (['cabecera','lineas']), $error
$cab          = $pedido['cabecera'];
$tituloPagina = 'Solicitar devolución';
require __DIR__ . '/layout/header.php';
?>

<section class="seccion-auth">
    <div class="tarjeta-auth tarjeta-auth--ancha">

        <h1 class="titulo-pagina">↩️ Solicitar devolución</h1>
        <p class="auth-subtitulo">
            Pedido #<?= $cab['id'] ?> · <?= date('d/m/Y', strtotime($cab['created_at'])) ?> ·
            <strong><?= number_format($cab['total'], 2, ',', '.') ?> €</strong>
        </p>

        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="index.php?accion=solicitar_devolucion" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="pedido_id" value="<?= $cab['id'] ?>">

            <div class="campo-form">
                <label for="motivo">Motivo de la devolución *</label>
                <select id="motivo" name="motivo" required>
                    <option value="">— Selecciona un motivo —</option>
                    <option value="Producto defectuoso">Producto defectuoso o dañado</option>
                    <option value="No es lo que esperaba">No es lo que esperaba</option>
                    <option value="Talla o modelo incorrecto">Talla o modelo incorrecto</option>
                    <option value="Llegó tarde">El pedido llegó tarde</option>
                    <option value="Producto incorrecto">Me enviaron el producto incorrecto</option>
                    <option value="Otro">Otro motivo</option>
                </select>
            </div>

            <div class="campo-form">
                <label for="descripcion">Cuéntanos más (opcional)</label>
                <textarea id="descripcion" name="descripcion" rows="4"
                          placeholder="Describe el problema con más detalle..."></textarea>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn btn-primario">Solicitar devolución</button>
                <a href="index.php?pagina=pedido&id=<?= $cab['id'] ?>" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>

        <div class="alerta alerta-exito">
            <strong>✓ Política de devoluciones</strong><br>
            Tienes 3 días desde la recepción del pedido para solicitar una devolución.
            El reembolso se procesará en 5–7 días hábiles una vez recibamos el producto.
        </div>

    </div>
</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
