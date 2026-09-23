<?php
// views/checkout.php
// Variables: $carrito, $usuario (datos del usuario para pre-rellenar), $error
$tituloPagina = 'Finalizar compra';
require __DIR__ . '/layout/header.php';

$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<section class="seccion-checkout">

    <h1 class="titulo-pagina">Finalizar compra</h1>

    <?php if (!empty($error)): ?>
        <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="checkout-layout">

        <!-- ── Formulario ───────────────────────────────────── -->
        <div class="checkout-form-wrap">
            <form action="index.php?accion=confirmar_pedido" method="POST" id="form-checkout" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- SECCIÓN 1: Destinatario -->
                <h2 class="checkout-seccion-titulo">1. Datos del destinatario</h2>

                <div class="form-row">
                    <div class="campo-form">
                        <label for="nombre_destinatario">Nombre y apellidos *</label>
                        <input type="text" id="nombre_destinatario" name="nombre_destinatario"
                               required autocomplete="name"
                               value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
                    </div>
                    <div class="campo-form">
                        <label for="telefono_envio">Teléfono de contacto</label>
                        <input type="tel" id="telefono_envio" name="telefono_envio"
                               autocomplete="tel" placeholder="+34 600 000 000"
                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                    </div>
                </div>

                <!-- SECCIÓN 2: Dirección de envío -->
                <h2 class="checkout-seccion-titulo">2. Dirección de envío</h2>

                <div class="campo-form">
                    <label for="calle">Calle y número *</label>
                    <input type="text" id="calle" name="calle"
                           required autocomplete="street-address" placeholder="Calle Mayor, 42, 3º A"
                           value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="campo-form">
                        <label for="ciudad">Ciudad *</label>
                        <input type="text" id="ciudad" name="ciudad"
                               required autocomplete="address-level2"
                               value="<?= htmlspecialchars($usuario['ciudad'] ?? '') ?>">
                    </div>
                    <div class="campo-form campo-cp">
                        <label for="codigo_postal">Código postal *</label>
                        <input type="text" id="codigo_postal" name="codigo_postal"
                               required autocomplete="postal-code" maxlength="10"
                               value="<?= htmlspecialchars($usuario['codigo_postal'] ?? '') ?>">
                    </div>
                </div>

                <div class="campo-form">
                    <label for="pais">País *</label>
                    <select id="pais" name="pais" autocomplete="country-name">
                        <?php
                        $paises = ['España','Francia','Italia','Portugal','Alemania','Reino Unido','Otros'];
                        foreach ($paises as $pais):
                        ?>
                            <option value="<?= $pais ?>"><?= $pais ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- SECCIÓN 3: Método de pago (simulado) -->
                <h2 class="checkout-seccion-titulo">3. Método de pago</h2>

                <div class="aviso-pago">
                    🔒 Pago simulado — en un proyecto real se integraría Stripe o similar.
                    Los datos de tarjeta <strong>no se procesan ni almacenan</strong>.
                </div>

                <div class="campo-form">
                    <label for="tarjeta">Número de tarjeta</label>
                    <input type="text" id="tarjeta" name="tarjeta" class="input-tarjeta"
                           placeholder="1234 5678 9012 3456" maxlength="19"
                           autocomplete="cc-number" inputmode="numeric">
                </div>

                <div class="campo-form">
                    <label for="titular">Titular de la tarjeta</label>
                    <input type="text" id="titular" name="titular"
                           placeholder="NOMBRE APELLIDO" autocomplete="cc-name">
                </div>

                <div class="form-row">
                    <div class="campo-form">
                        <label for="caducidad">Caducidad</label>
                        <input type="text" id="caducidad" name="caducidad"
                               placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
                    </div>
                    <div class="campo-form campo-cp">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv"
                               placeholder="123" maxlength="4"
                               autocomplete="cc-csc" inputmode="numeric">
                    </div>
                </div>

                <button type="submit" class="btn btn-primario btn-grande btn-block btn-pagar">
                    🔒 Confirmar pedido — <?= number_format($total, 2, ',', '.') ?> €
                </button>
            </form>
        </div>

        <!-- ── Resumen del pedido ────────────────────────────── -->
        <div class="checkout-resumen">
            <h2>Tu pedido</h2>
            <?php foreach ($carrito as $item): ?>
                <div class="resumen-linea">
                    <span><?= htmlspecialchars($item['icono']) ?> <?= htmlspecialchars($item['nombre']) ?> × <?= $item['cantidad'] ?></span>
                    <span><?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €</span>
                </div>
            <?php endforeach; ?>
            <div class="resumen-linea">
                <span>Envío</span>
                <span class="texto-verde">Gratis</span>
            </div>
            <div class="resumen-linea resumen-total">
                <span>Total</span>
                <span><?= number_format($total, 2, ',', '.') ?> €</span>
            </div>
            <p class="checkout-garantia">✓ Entrega estimada en 24–36 horas</p>
            <p class="checkout-garantia">✓ Devolución gratuita en 3 días</p>
        </div>

    </div>

</section>

<?php require __DIR__ . '/layout/footer.php'; ?>
