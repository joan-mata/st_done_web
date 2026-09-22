// tienda.js — TechShop v2
document.addEventListener('DOMContentLoaded', function () {

    // ── Carrito drawer (popup lateral) ────────────────────────
    const btnAbrir   = document.getElementById('btn-abrir-carrito');
    const btnCerrar  = document.getElementById('btn-cerrar-carrito');
    const overlay    = document.getElementById('carrito-overlay');
    const drawer     = document.getElementById('carrito-drawer');

    function abrirCarrito() {
        drawer.classList.add('abierto');
        overlay.classList.add('visible');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden'; // Evitar scroll de fondo
    }

    function cerrarCarrito() {
        drawer.classList.remove('abierto');
        overlay.classList.remove('visible');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (btnAbrir)  btnAbrir.addEventListener('click', abrirCarrito);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarCarrito);
    if (overlay)   overlay.addEventListener('click', cerrarCarrito);

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('abierto')) {
            cerrarCarrito();
        }
    });

    // ── Formato automático del número de tarjeta ──────────────
    const inputTarjeta = document.getElementById('tarjeta');
    if (inputTarjeta) {
        inputTarjeta.addEventListener('input', function () {
            // Eliminar todo lo que no sea número
            let val = this.value.replace(/\D/g, '');
            // Insertar espacio cada 4 dígitos
            val = val.replace(/(.{4})/g, '$1 ').trim();
            this.value = val;
        });
    }

    // ── Formato automático de la caducidad ────────────────────
    const inputCaducidad = document.getElementById('caducidad');
    if (inputCaducidad) {
        inputCaducidad.addEventListener('input', function () {
            let val = this.value.replace(/\D/g, '');
            if (val.length >= 2) {
                val = val.slice(0, 2) + '/' + val.slice(2, 4);
            }
            this.value = val;
        });
    }

    // ── Validación checkout: contraseñas / confirmación ────────
    const formCheckout = document.getElementById('form-checkout');
    if (formCheckout) {
        formCheckout.addEventListener('submit', function (e) {
            const nombre = document.getElementById('nombre_destinatario');
            const calle  = document.getElementById('calle');
            const ciudad = document.getElementById('ciudad');
            const cp     = document.getElementById('codigo_postal');
            const campos = [nombre, calle, ciudad, cp];
            let ok = true;
            campos.forEach(function (campo) {
                if (campo && campo.value.trim() === '') {
                    campo.style.borderColor = '#dc2626';
                    ok = false;
                } else if (campo) {
                    campo.style.borderColor = '';
                }
            });
            if (!ok) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // ── Validación registro: contraseñas coinciden ─────────────
    const formRegistro = document.querySelector('form[action*="registro"]');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function (e) {
            const p1 = document.getElementById('password');
            const p2 = document.getElementById('password2');
            if (p1 && p2 && p1.value !== p2.value) {
                e.preventDefault();
                p2.style.borderColor = '#dc2626';
                const msg = document.createElement('p');
                msg.style.cssText = 'color:#dc2626;font-size:.83rem;margin-top:.3rem;';
                msg.textContent = 'Las contraseñas no coinciden.';
                if (!p2.parentElement.querySelector('.err-pass')) {
                    msg.className = 'err-pass';
                    p2.parentElement.appendChild(msg);
                }
                p2.focus();
            }
        });
    }

    // ── Confirmación al vaciar carrito ─────────────────────────
    const formVaciar = document.querySelector('form[action*="vaciar_carrito"]');
    if (formVaciar) {
        formVaciar.addEventListener('submit', function (e) {
            if (!confirm('¿Vaciar el carrito?')) e.preventDefault();
        });
    }

});
