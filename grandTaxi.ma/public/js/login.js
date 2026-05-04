// ─── PENDING BANNER
// Show a banner if the driver was redirected here after registration
(function checkPending() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('pending') === '1') {
        const el = document.getElementById('error-msg');
        if (el) {
            el.innerHTML = `
                ⏳ <strong>Compte en attente de validation.</strong><br>
                <span style="font-size:0.85em;">
                  Un administrateur doit approuver votre compte avant que vous puissiez accéder
                  à votre tableau de bord conducteur.
                </span>`;
            el.style.background = 'rgba(234,179,8,0.12)';
            el.style.borderColor = 'rgba(234,179,8,0.3)';
            el.style.color = '#fbbf24';
            el.classList.remove('hidden');
        }
    }
})();

async function login() {
    try {
        const res = await axios.post('/api/login', {
            email:    document.getElementById('email').value,
            password: document.getElementById('password').value,
        });

        localStorage.setItem('api_token', res.data.access_token);
        localStorage.setItem('user', JSON.stringify(res.data.user));

        if (res.data.user.role === 'admin') {
            window.location.href = '/admin/dashboard';
        } else if (res.data.user.role === 'driver') {
            window.location.href = '/driver/dashboard';
        } else {
            window.location.href = '/index';
        }
    } catch (err) {
        const data = err.response?.data;
        const el = document.getElementById('error-msg');

        // Special handling for pending drivers
        if (data?.status === 'pending') {
            el.innerHTML = `
                ⏳ <strong>Compte en attente de validation.</strong><br>
                <span style="font-size:0.85em;">
                  Un administrateur doit approuver votre compte avant que vous puissiez accéder
                  à votre tableau de bord conducteur.
                </span>`;
            el.style.background = 'rgba(234,179,8,0.12)';
            el.style.borderColor = 'rgba(234,179,8,0.3)';
            el.style.color = '#fbbf24';
        } else {
            el.innerText = data?.message || 'Erreur de connexion';
            el.style.background = '';
            el.style.borderColor = '';
            el.style.color = '';
        }
        el.classList.remove('hidden');
    }
}
