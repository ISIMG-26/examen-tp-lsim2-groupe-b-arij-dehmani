/** auth.php — connexion / inscription (AJAX vers back/auth_handler.php) */

function authShowTab(tab) {
    document.getElementById('tab-login').classList.toggle('active', tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
    document.getElementById('login-box').classList.toggle('active', tab === 'login');
    document.getElementById('register-box').classList.toggle('active', tab === 'register');
    const m = document.getElementById('msg');
    if (m) { m.className = 'alert'; m.textContent = ''; }
}

function authMsg(text, ok) {
    const el = document.getElementById('msg');
    if (!el) return;
    el.textContent = text;
    el.className = 'alert show ' + (ok ? 'success' : 'error');
}

async function authLogin() {
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    if (!email || !password) return authMsg('Email et mot de passe requis.', false);
    const fd = new FormData();
    fd.append('action', 'connexion');
    fd.append('email', email);
    fd.append('password', password);
    try {
        const data = await (await fetch('back/auth_handler.php', { method: 'POST', body: fd })).json();
        if (data.success) {
            authMsg(data.message, true);
            setTimeout(() => { location.href = data.redirect; }, 300);
        } else authMsg(data.message, false);
    } catch (e) {
        authMsg('Erreur réseau.', false);
    }
}

async function authRegister() {
    const prenom = document.getElementById('reg-prenom').value.trim();
    const nom = document.getElementById('reg-nom').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    const confirm = document.getElementById('reg-confirm').value;
    if (!prenom || !nom || !email || password.length < 6 || password !== confirm) {
        return authMsg('Vérifiez les champs.', false);
    }
    const fd = new FormData();
    fd.append('action', 'inscription');
    fd.append('nom', nom);
    fd.append('prenom', prenom);
    fd.append('email', email);
    fd.append('password', password);
    fd.append('confirm', confirm);
    try {
        const data = await (await fetch('back/auth_handler.php', { method: 'POST', body: fd })).json();
        if (data.success) {
            authMsg(data.message, true);
            setTimeout(() => authShowTab('login'), 500);
        } else authMsg(data.message, false);
    } catch (e) {
        authMsg('Erreur réseau.', false);
    }
}
