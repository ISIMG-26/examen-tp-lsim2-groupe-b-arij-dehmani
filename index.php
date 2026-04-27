<?php
require_once 'back/config.php';
$user_name=isset($_SESSION['prenom']) ? $_SESSION['prenom'] : null;
$is_admin= isAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kah & ena — Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!--navbar-->
<nav>
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">CoffeeShop</a>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Menu</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <?php if ($user_name): ?>
                <li><a href="#" onclick="logout();return false;"><?= htmlspecialchars($user_name) ?> (logout)</a></li>
            <?php else: ?>
                <li><a href="auth.php">Login</a></li>
            <?php endif; ?>
            <li><a href="#" onclick="openCart();return false;">Panier <span class="cart-count">0</span></a></li>
        </ul>
    </div>
</nav>
<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <span class="hero-badge">☕ Ouvert tous les jours</span>
        <h1>Le café qui<br><em>réveille votre journée</em></h1>
        <p>Des grains sélectionnés autour du monde, torréfiés avec passion et préparés avec soin. Chaque tasse est une expérience unique.</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap"></div>
    </div>
</section>

<main class="section">
    <input type="text" id="search-input" class="search-input" placeholder="Rechercher...">
    <div id="categories-bar" class="categories-bar"></div>
    <div id="products-grid" class="products-grid"></div>
</main>

<div class="cart-overlay" id="cart-overlay"></div>
<div class="cart-sidebar" id="cart-sidebar">
    <div class="cart-header">
        <h2>Panier</h2>
        <button class="cart-close" onclick="closeCart()">x</button>
    </div>
    <div class="cart-items" id="cart-items"></div>
    <div class="cart-footer">
        <div class="cart-total">
            <span>Total</span>
            <span id="cart-total">0.00 €</span>
        </div>
        <button class="btn-primary btn-full" id="checkout-btn" onclick="passerCommande()">Commander</button>
        <?php if (!$user_name): ?><a href="auth.php">Connectez-vous pour commander</a><?php endif; ?>
    </div>
</div>

<script src="js/common.js"></script>
<script src="js/app.js"></script>

</body>
</html>
