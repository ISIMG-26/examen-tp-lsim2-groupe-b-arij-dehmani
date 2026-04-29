<?php
require_once 'back/config.php';
$user_name = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : null;
$is_admin = isAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brew & Co — Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">Brew<span>&</span>Co</a>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Accueil</a></li>
            <li><a href="categories.php">Menu</a></li>
            <?php if ($is_admin): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <?php endif; ?>
            <?php if ($user_name): ?>
            <li><a href="#" onclick="logout()">👋 <?= htmlspecialchars($user_name) ?></a></li>
            <?php else: ?>
            <li><a href="auth.php">Se connecter</a></li>
            <?php endif; ?>
            <li>
                <a href="#" class="nav-cart-btn" onclick="openCart(); return false;">
                    🛒 Panier <span class="cart-count">0</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <span class="hero-badge">☕ Ouvert tous les jours</span>
        <h1>Le café qui<br><em>réveille votre journée</em></h1>
        <p>Des grains sélectionnés aux quatre coins du monde, torréfiés avec passion et préparés avec soin. Chaque tasse est une expérience unique.</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap">
            <a href="categories.php" class="btn-primary">🧾 Voir le menu</a>
            <a href="categories.php" class="btn-secondary">Commander</a>
        </div>
    </div>
</section>

<!-- SECTION PRODUITS -->
<div class="section">
    <h2 class="section-title">Nos <span>Spécialités</span></h2>
    <p class="section-subtitle">Découvrez notre sélection du moment</p>

    <!-- Barre de recherche -->
    <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" id="search-input" class="search-input" placeholder="Rechercher une boisson, pâtisserie...">
    </div>

    <!-- Filtres catégories -->
    <div class="categories-bar" id="categories-bar"></div>

    <!-- Grille produits -->
    <div class="products-grid" id="products-grid">
        <div class="loading"><div class="spinner"></div> Chargement...</div>
    </div>
</div>

<!-- SECTION VALEURS -->
<div style="background:var(--espresso);padding:4rem 2rem;margin-top:2rem">
    <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem;text-align:center">
        <div>
            <div style="font-size:2.5rem;margin-bottom:0.8rem">🌱</div>
            <h3 style="font-family:'Playfair Display',serif;color:var(--latte);margin-bottom:0.5rem">Éthique & Durable</h3>
            <p style="color:var(--cream);opacity:0.7;font-size:0.9rem">Café issu du commerce équitable, respect des producteurs.</p>
        </div>
        <div>
            <div style="font-size:2.5rem;margin-bottom:0.8rem">👨‍🍳</div>
            <h3 style="font-family:'Playfair Display',serif;color:var(--latte);margin-bottom:0.5rem">Fait Maison</h3>
            <p style="color:var(--cream);opacity:0.7;font-size:0.9rem">Pâtisseries fraîches préparées chaque matin par nos baristas.</p>
        </div>
        <div>
            <div style="font-size:2.5rem;margin-bottom:0.8rem">📍</div>
            <h3 style="font-family:'Playfair Display',serif;color:var(--latte);margin-bottom:0.5rem">Livraison Express</h3>
            <p style="color:var(--cream);opacity:0.7;font-size:0.9rem">Commandez en ligne, récupérez sans attendre.</p>
        </div>
        <div>
            <div style="font-size:2.5rem;margin-bottom:0.8rem">⭐</div>
            <h3 style="font-family:'Playfair Display',serif;color:var(--latte);margin-bottom:0.5rem">Top Qualité</h3>
            <p style="color:var(--cream);opacity:0.7;font-size:0.9rem">Grains arabica de spécialité, extraction parfaite à chaque fois.</p>
        </div>
    </div>
</div>

<!-- PANIER SIDEBAR -->
<div class="cart-overlay" id="cart-overlay"></div>
<div class="cart-sidebar" id="cart-sidebar">
    <div class="cart-header">
        <h2>🛒 Mon Panier</h2>
        <button class="cart-close" onclick="closeCart()">✕</button>
    </div>
    <div class="cart-items" id="cart-items"></div>
    <div class="cart-footer">
        <div class="cart-total">
            <span class="cart-total-label">Total</span>
            <span class="cart-total-amount" id="cart-total">0.00 €</span>
        </div>
        <button class="btn-primary btn-full" id="checkout-btn" onclick="passerCommande()">
            ☕ Commander
        </button>
        <?php if (!$user_name): ?>
        <p style="font-size:0.78rem;text-align:center;margin-top:0.8rem;color:var(--mocha)">
            <a href="auth.php" style="color:var(--caramel)">Connectez-vous</a> pour finaliser votre commande
        </p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p><strong>Brew & Co</strong> — Le café comme vous l'aimez &nbsp;☕&nbsp; © <?= date('Y') ?></p>
</footer>

<script src="js/app.js"></script>
<script>
function logout() {
    const fd = new FormData();
    fd.append('action', 'deconnexion');
    fetch('back/auth_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if(d.success) window.location.href = 'index.php'; });
}
</script>
</body>
</html>

