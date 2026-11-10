/**
 * Maneja el envío del formulario sin recargar la página.
 */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.php-email-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn     = form.querySelector('[type="submit"]');
    const aviso   = form.querySelector('.form-aviso');
    const original = btn.textContent;

    // Estado de carga
    btn.disabled = true;
    btn.textContent = 'Enviando…';
    aviso.className = 'form-aviso';
    aviso.textContent = '';

    try {
      const res  = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
      });

      const data = await res.json();

      if (data.ok) {
        aviso.className = 'form-aviso exito';
        aviso.textContent = '✓ ' + data.mensaje;
        form.reset();
      } else {
        const texto = Array.isArray(data.errores)
          ? data.errores.join(' · ')
          : data.mensaje;
        aviso.className = 'form-aviso error';
        aviso.textContent = texto;
      }

    } catch {
      aviso.className = 'form-aviso error';
      aviso.textContent = 'Error de conexión. Intentá de nuevo.';
    } finally {
      btn.disabled = false;
      btn.textContent = original;
    }
  });
});