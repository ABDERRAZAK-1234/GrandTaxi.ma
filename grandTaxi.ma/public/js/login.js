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
            } else if (res.data.user.role === 'driver'){
                window.location.href = '/driver/dashboard';
            }else{
                window.location.href = '/index';
            }
        } catch (err) {
            const msg = err.response?.data?.message || 'Erreur de connexion';
            const el = document.getElementById('error-msg');
            el.innerText = msg;
            el.classList.remove('hidden');
        }
    }
