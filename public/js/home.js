
/* -----------------------
 * Helpers e CSRF refresh
 * ---------------------*/
async function fetchCsrf() {
  try {
    const res = await fetch('http://localhost/Futurize.STM/auth/refresh-csrf');
    if (!res.ok) return;
    const json = await res.json();
    document.querySelector('meta[name="csrf-token"]').setAttribute('content', json.csrf);
    // tokenName normalmente csrf_test_name; se precisar, atualizar hidden inputs
  } catch (e) { console.warn('CSRF refresh failed', e); }
}

/* Modal management */
function openModal(tipo) {
  const m = document.getElementById(tipo + 'Modal');
  if (m) m.style.display = 'flex';
}
function closeModal(modalId) {
  const m = document.getElementById(modalId);
  if (m) m.style.display = 'none';
}
window.onclick = function(e) {
  document.querySelectorAll('.modal').forEach(m => { if (e.target === m) m.style.display = 'none'; });
}
function handleImageError(img, placeholderId) {
  img.style.display = 'none';
  document.getElementById(placeholderId).style.display = 'flex';
}

/* Exponential backoff util */
function wait(ms){ return new Promise(r=>setTimeout(r,ms)); }
async function postWithBackoff(url, body, attempts=3) {
  let delay = 500;
  for (let i=0;i<attempts;i++){
    try {
      const res = await fetch(url, body);
      return res;
    } catch (err) {
      if (i === attempts-1) throw err;
      await wait(delay);
      delay *= 2;
    }
  }
}

/* Form submit common */
function bindLoginForm(formId, tipo) {
  const form = document.getElementById(formId);
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msgEl = document.getElementById(tipo + 'Message');
    const btn = document.getElementById(tipo + 'SubmitBtn');
    const loading = document.getElementById(tipo + 'Loading');
    const btnText = document.getElementById(tipo + 'BtnText');

    btn.classList.add('btn-loading');
    loading.style.display = 'inline-block';
    btnText.textContent = 'Entrando...';
    msgEl.innerHTML = '';

    const formData = new FormData(form);
    formData.append('tipo', tipo);
    const data = new URLSearchParams(formData).toString(); // força string


    try {
      const res = await postWithBackoff('http://localhost/Futurize.STM/public/index.php/auth/login', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: data
      });

      if (!res.ok) {
        const err = await res.json().catch(()=>({message:'Erro inesperado'}));
        msgEl.innerHTML = `<div class="alert alert-error">${err.message||err.msg}</div>`;
        // refresh csrf on 419/403
        if (res.status === 419 || res.status === 403) await fetchCsrf();
      } else {
        const json = await res.json();
        msgEl.innerHTML = `<div class="alert alert-success">${json.message}</div>`;
        // redireciona
        setTimeout(()=> window.location = json.redirect || 'admin/dashboard', 900);
      }
    } catch (err) {
      msgEl.innerHTML = `<div class="alert alert-error">Erro de conexão. Tente novamente.</div>`;
    } finally {
      btn.classList.remove('btn-loading');
      loading.style.display = 'none';
      btnText.textContent = 'Entrar';
    }
  });
}

/* bind: initialize login forms on DOMContentLoaded (silent in production) */
document.addEventListener('DOMContentLoaded', function() {
  const adminForm = document.getElementById('adminForm');
  const servidorForm = document.getElementById('servidorForm');

  if (adminForm) bindLoginForm('adminForm','admin');
  if (servidorForm) bindLoginForm('servidorForm','servidor');
});

