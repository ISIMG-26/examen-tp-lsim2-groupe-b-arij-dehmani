<?php
require_once 'back/config.php';
$user_name = $_SESSION['prenom'] ?? null;
$is_admin = isAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
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
            <span id="cart-total">0.00 TND</span>
        </div>
        <button class="btn-primary btn-full" id="checkout-btn" onclick="passerCommande()">Commander</button>
        <?php if (!$user_name): ?><a href="auth.php">Connectez-vous pour commander</a><?php endif; ?>
    </div>
</div>


<script src="js/menu.js"></script>
</body>
</html>
