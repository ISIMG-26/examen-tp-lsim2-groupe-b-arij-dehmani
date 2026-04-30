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
        <button type="button" class="auth-tab active" id="tab-login" onclick="authShowTab('login')">Login</button>
        <button type="button" class="auth-tab" id="tab-register" onclick="authShowTab('register')">Register</button>
    </div>

    <div id="login-box" class="auth-form active">
        <input id="login-email" class="form-input" type="email" placeholder="email">
        <input id="login-password" class="form-input" type="password" placeholder="password">
        <button type="button" class="btn-primary btn-full" onclick="authLogin()">Se connecter</button>
    </div>

    <div id="register-box" class="auth-form">
        <input id="reg-prenom" class="form-input" type="text" placeholder="prenom">
        <input id="reg-nom" class="form-input" type="text" placeholder="nom">
        <input id="reg-email" class="form-input" type="email" placeholder="email">
        <input id="reg-password" class="form-input" type="password" placeholder="password (min 6)">
        <input id="reg-confirm" class="form-input" type="password" placeholder="confirm password">
        <button type="button" class="btn-primary btn-full" onclick="authRegister()">Creer compte</button>
    </div>
    <p><a href="index.php">Retour menu</a></p>
</div></div>

<script src="js/auth.js"></script>
</body>
</html>
