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
    <title>Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page"><div class="auth-card">
    <h2>Connexion / Inscription</h2>
    <div id="msg" class="alert"></div>
    <div class="auth-tabs">
        <button class="auth-tab active" id="tab-login" onclick="showTab('login')">Login</button>
        <button class="auth-tab" id="tab-register" onclick="showTab('register')">Register</button>
    </div>

    <div id="login-box" class="auth-form active">
        <input id="login-email" class="form-input" type="email" placeholder="email">
        <input id="login-password" class="form-input" type="password" placeholder="password">
        <button class="btn-primary btn-full" onclick="authLogin()">Se connecter</button>
    </div>

    <div id="register-box" class="auth-form">
        <input id="reg-prenom" class="form-input" type="text" placeholder="prenom">
        <input id="reg-nom" class="form-input" type="text" placeholder="nom">
        <input id="reg-email" class="form-input" type="email" placeholder="email">
        <input id="reg-password" class="form-input" type="password" placeholder="password (min 6)">
        <input id="reg-confirm" class="form-input" type="password" placeholder="confirm password">
        <button class="btn-primary btn-full" onclick="authRegister()">Creer compte</button>
    </div>
    <p><a href="index.php">Retour menu</a></p>
</div></div>

<script src="js/auth.js"></script>
<script>
function showTab(tab) {
    // Change d'onglet login/register et nettoie le message d'état.
    document.getElementById('tab-login').classList.toggle('active', tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
    document.getElementById('login-box').classList.toggle('active', tab === 'login');
    document.getElementById('register-box').classList.toggle('active', tab === 'register');
    document.getElementById('msg').className = 'alert';
    document.getElementById('msg').textContent = '';
}

function showMsg(text, ok) {
    // Affiche un message utilisateur (succès / erreur).
    const el = document.getElementById('msg');
    el.textContent = text;
    el.className = 'alert show ' + (ok ? 'success' : 'error');
}

async function handleLogin() {
    // AJAX POST vers auth_handler.php pour se connecter.
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    if (!email || !password) return showMsg('Email et mot de passe requis.', false);

    const fd = new FormData();
    fd.append('action', 'connexion');
    fd.append('email', email);
    fd.append('password', password);

    try {
        const res = await fetch('back/auth_handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            // Le backend renvoie une URL de redirection selon le rôle.
            showMsg(data.message, true);
            setTimeout(() => window.location.href = data.redirect, 400);
        } else {
            showMsg(data.message, false);
        }
    } catch (e) {
        showMsg('Erreur reseau.', false);
    }
}

async function handleRegister() {
    // AJAX POST vers auth_handler.php pour créer un compte.
    const prenom = document.getElementById('reg-prenom').value.trim();
    const nom = document.getElementById('reg-nom').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    const confirm = document.getElementById('reg-confirm').value;
    if (!prenom || !nom || !email || password.length < 6 || password !== confirm) {
        return showMsg('Verifiez les champs du formulaire.', false);
    }

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
            showMsg(data.message, true);
            setTimeout(() => showTab('login'), 600);
        } else {
            showMsg(data.message, false);
        }
    } catch (e) {
        showMsg('Erreur reseau.', false);
    }
}
</script>
</script>
</body>
</html>
