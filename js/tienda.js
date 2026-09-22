// tienda.js — TechShop
// ============================================================
// JavaScript del lado del cliente.
// Solo mejoras de UX: no contiene lógica de negocio
// (esa está en PHP, en el servidor).
// ============================================================

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {

    // ── Confirmación al vaciar el carrito ─────────────────────
    const formVaciar = document.querySelector('form[action*="vaciar_carrito"]');
    if (formVaciar) {
        formVaciar.addEventListener('submit', function (e) {
            if (!confirm('¿Seguro que quieres vaciar el carrito?')) {
                e.preventDefault();
            }
        });
    }

    // ── Validación del formulario de registro en el cliente ───
    // (El servidor también valida; esto es solo una mejora de UX)
    const formRegistro = document.querySelector('form[action*="registro"]');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function (e) {
            const pass1 = document.getElementById('password');
            const pass2 = document.getElementById('password2');

            if (pass1 && pass2 && pass1.value !== pass2.value) {
                e.preventDefault();
                mostrarErrorForm(pass2, 'Las contraseñas no coinciden.');
                pass2.focus();
            }
        });
    }

    // ── Resaltar el enlace de navegación activo ────────────────
    const urlActual = window.location.href;
    document.querySelectorAll('.nav-link').forEach(function (enlace) {
        if (enlace.href === urlActual) {
            enlace.style.background = 'var(--color-fondo)';
            enlace.style.color      = 'var(--color-texto)';
            enlace.style.fontWeight = '600';
        }
    });

});

// Función auxiliar: mostrar mensaje de error bajo un campo
function mostrarErrorForm(campo, mensaje) {
    // Eliminar error anterior si existe
    const errorAnterior = campo.parentElement.querySelector('.error-campo');
    if (errorAnterior) {
        errorAnterior.remove();
    }

    const div = document.createElement('p');
    div.className = 'error-campo';
    div.style.cssText = 'color:#dc2626; font-size:.83rem; margin-top:.3rem;';
    div.textContent = mensaje;
    campo.parentElement.appendChild(div);

    // Borde rojo en el campo
    campo.style.borderColor = '#dc2626';
    campo.addEventListener('input', function () {
        campo.style.borderColor = '';
        div.remove();
    }, { once: true });
}
