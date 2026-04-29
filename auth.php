<?php
require_once 'back/config.php';
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'dashboard.php' : 'index.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Brew & Co</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-logo">
            <div class="auth-logo-text">Brew<span>&</span>Co</div>
            <p style="color:var(--mocha);font-size:0.88rem;margin-top:0.3rem">Bienvenue dans notre univers café ☕</p>
        </div>

        <!-- Tabs -->
        <div class="auth-tabs">
            <button class="auth-tab active" id="tab-login" onclick="switchTab('login')">Se connecter</button>
            <button class="auth-tab" id="tab-register" onclick="switchTab('register')">S'inscrire</button>
        </div>

        <!-- Alert -->
        <div class="alert" id="auth-alert"></div>

        <!-- CONNEXION FORM -->
        <div class="auth-form active" id="form-login">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" id="login-email" class="form-input" placeholder="votre@email.com">
                <div class="form-error" id="err-login-email">Email requis</div>
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" id="login-password" class="form-input" placeholder="••••••••">
                <div class="form-error" id="err-login-password">Mot de passe requis</div>
            </div>
            <button class="btn-primary btn-full" onclick="handleLogin()" id="btn-login">
                Se connecter →
            </button>
            <p style="text-align:center;margin-top:1rem;font-size:0.83rem;color:var(--mocha)">
                Pas encore de compte ? 
                <a href="#" onclick="switchTab('register')" style="color:var(--caramel);font-weight:700">S'inscrire</a>
            </p>
        </div>

        <!-- INSCRIPTION FORM -->
        <div class="auth-form" id="form-register">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" id="reg-prenom" class="form-input" placeholder="Alice">
                    <div class="form-error" id="err-reg-prenom">Champ requis</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" id="reg-nom" class="form-input" placeholder="Dupont">
                    <div class="form-error" id="err-reg-nom">Champ requis</div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" id="reg-email" class="form-input" placeholder="votre@email.com">
                <div class="form-error" id="err-reg-email">Email invalide</div>
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" id="reg-password" class="form-input" placeholder="Min. 6 caractères"
                    oninput="checkPasswordStrength(this.value)">
                <div class="form-error" id="err-reg-password">Min. 6 caractères requis</div>
                <div id="pwd-strength" style="height:4px;border-radius:2px;margin-top:6px;transition:all 0.3s;background:var(--cream)"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" id="reg-confirm" class="form-input" placeholder="Répétez votre mot de passe">
                <div class="form-error" id="err-reg-confirm">Les mots de passe ne correspondent pas</div>
            </div>
            <button class="btn-primary btn-full" onclick="handleRegister()" id="btn-register">
                Créer mon compte →
            </button>
            <p style="text-align:center;margin-top:1rem;font-size:0.83rem;color:var(--mocha)">
                Déjà inscrit ? 
                <a href="#" onclick="switchTab('login')" style="color:var(--caramel);font-weight:700">Se connecter</a>
            </p>
        </div>

        <div style="text-align:center;margin-top:1.5rem">
            <a href="index.php" style="font-size:0.82rem;color:var(--mocha);text-decoration:none;opacity:0.7">
                ← Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<script>
// Tab switch
function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('form-' + tab).classList.add('active');
    hideAlert();
    clearErrors();
}

// Alert
function showAlert(msg, type) {
    const el = document.getElementById('auth-alert');
    el.textContent = msg;
    el.className = 'alert show ' + type;
}
function hideAlert() {
    document.getElementById('auth-alert').className = 'alert';
}

// Form error helpers
function setError(id, show) {
    const el = document.getElementById(id);
    if (show) el.classList.add('show');
    else el.classList.remove('show');
}
function clearErrors() {
    document.querySelectorAll('.form-error').forEach(e => e.classList.remove('show'));
    document.querySelectorAll('.form-input').forEach(i => i.classList.remove('error'));
}

// Password strength indicator
function checkPasswordStrength(val) {
    const bar = document.getElementById('pwd-strength');
    if (!bar) return;
    let strength = 0;
    if (val.length >= 6) strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;
    const colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#27ae60'];
    const widths = ['0%', '20%', '40%', '60%', '80%', '100%'];
    bar.style.background = colors[strength] || 'var(--cream)';
    bar.style.width = widths[Math.min(strength, 5)];
}

// LOGIN
async function handleLogin() {
    clearErrors(); hideAlert();
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    let valid = true;

    if (!email) { setError('err-login-email', true); valid = false; }
    if (!password) { setError('err-login-password', true); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('btn-login');
    btn.disabled = true; btn.textContent = 'Connexion...';

    const fd = new FormData();
    fd.append('action', 'connexion');
    fd.append('email', email);
    fd.append('password', password);

    try {
        const res = await fetch('back/auth_handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showAlert('✓ ' + data.message, 'success');
            setTimeout(() => window.location.href = data.redirect, 800);
        } else {
            showAlert(data.message, 'error');
        }
    } catch {
        showAlert('Erreur réseau. Réessayez.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Se connecter →';
}

// REGISTER
async function handleRegister() {
    clearErrors(); hideAlert();
    const prenom = document.getElementById('reg-prenom').value.trim();
    const nom = document.getElementById('reg-nom').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    const confirm = document.getElementById('reg-confirm').value;
    let valid = true;

    if (!prenom) { setError('err-reg-prenom', true); valid = false; }
    if (!nom) { setError('err-reg-nom', true); valid = false; }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setError('err-reg-email', true); valid = false; }
    if (password.length < 6) { setError('err-reg-password', true); valid = false; }
    if (password !== confirm) { setError('err-reg-confirm', true); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('btn-register');
    btn.disabled = true; btn.textContent = 'Création...';

    const fd = new FormData();
    fd.append('action', 'inscription');
    fd.append('nom', nom);
    fd.append('prenom', prenom);
    fd.append('email', email);
    fd.append('password', password);
    fd.append('confirm', confirm);

    try {
        const res = await fetch('back/auth_handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showAlert('🎉 ' + data.message, 'success');
            setTimeout(() => switchTab('login'), 2000);
        } else {
            showAlert(data.message, 'error');
        }
    } catch {
        showAlert('Erreur réseau. Réessayez.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Créer mon compte →';
}

// Vérification email en temps réel (AJAX)
document.getElementById('reg-email')?.addEventListener('blur', async function() {
    const email = this.value.trim();
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return;
    // Vérification email déjà pris (feature AJAX)
    const fd = new FormData();
    fd.append('action', 'check_email');
    fd.append('email', email);
    // Note: cette vérification est gérée côté serveur lors de l'inscription
});

// Enter key support
document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        if (document.getElementById('form-login').classList.contains('active')) handleLogin();
        else handleRegister();
    }
});
</script>
</body>
</html>
