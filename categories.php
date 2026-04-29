<?php
require_once 'back/config.php';
$conn = getConnection();
$categories = $conn->query("SELECT * FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);
$user_name = $_SESSION['prenom'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Brew & Co</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">Brew<span>&</span>Co</a>
        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="categories.php" class="active">Menu</a></li>
            <?php if (isAdmin()): ?>
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

<!-- Page Hero -->
<div class="page-hero">
    <h1>Notre <span>Menu</span></h1>
    <p style="opacity:0.8;margin-top:0.5rem">Explorez toutes nos catégories et trouvez votre préférence</p>
</div>

<div class="section">
    <!-- Barre de recherche -->
    <div class="search-wrap" style="max-width:100%;margin-bottom:2rem">
        <span class="search-icon">🔍</span>
        <input type="text" id="search-input" class="search-input" placeholder="Rechercher dans le menu...">
    </div>

    <!-- Navigation rapide par catégorie -->
    <div class="categories-bar" id="categories-bar"></div>

    <!-- Grille produits dynamique -->
    <div class="products-grid" id="products-grid">
        <div class="loading"><div class="spinner"></div> Chargement du menu...</div>
    </div>
</div>

<!-- Sections détaillées par catégorie (PHP + MySQL) -->
<div class="section" style="padding-top:0">
    <h2 class="section-title" style="margin-bottom:2rem">Parcourir par <span>Catégorie</span></h2>
    <?php foreach ($categories as $cat):
        $produits = $conn->query("SELECT * FROM produits WHERE categorie_id = {$cat['id']} AND disponible = 1 ORDER BY prix ASC")->fetch_all(MYSQLI_ASSOC);
        if (empty($produits)) continue;
        $icons = ['Cafés Chauds'=>'☕','Boissons Froides'=>'🧋','Pâtisseries'=>'🥐','Sandwichs'=>'🥪','Thés & Infusions'=>'🍵'];
        $icone = $icons[$cat['nom']] ?? '☕';
    ?>
    <div class="cat-section" id="cat-<?= $cat['id'] ?>">
        <div class="cat-section-title">
            <span class="cat-section-icon"><?= $icone ?></span>
            <?= htmlspecialchars($cat['nom']) ?>
            <span style="font-size:0.9rem;color:var(--mocha);font-weight:400">(<?= count($produits) ?> produits)</span>
        </div>
        <div class="products-grid">
            <?php foreach ($produits as $p): ?>
            <div class="product-card">
                <div class="product-img-wrap">
                    <img class="product-img"
                         src="<?= htmlspecialchars($p['image'] ?? '') ?>"
                         alt="<?= htmlspecialchars($p['nom']) ?>"
                         onerror="this.parentElement.innerHTML='<div class=\'product-img-fallback\'><?= $icone ?></div>'">
                </div>
                <div class="product-body">
                    <span class="product-cat-tag"><?= htmlspecialchars($cat['nom']) ?></span>
                    <div class="product-name"><?= htmlspecialchars($p['nom']) ?></div>
                    <div class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></div>
                    <div class="product-footer">
                        <span class="product-price"><?= number_format($p['prix'], 2) ?> €</span>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nom'])) ?>', <?= $p['prix'] ?>, '<?= $icone ?>')"
                            title="Ajouter au panier">+</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; $conn->close(); ?>
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
        <button class="btn-primary btn-full" id="checkout-btn" onclick="passerCommande()">☕ Commander</button>
        <?php if (!$user_name): ?>
        <p style="font-size:0.78rem;text-align:center;margin-top:0.8rem;color:var(--mocha)">
            <a href="auth.php" style="color:var(--caramel)">Connectez-vous</a> pour finaliser
        </p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p><strong>Brew & Co</strong> — Le café comme vous l'aimez &nbsp;☕&nbsp; © <?= date('Y') ?></p>
</footer>

<script src="js/app.js"></script>
<script>
// Ne pas recharger les produits avec AJAX ici (déjà affichés par PHP)
// Mais garder la recherche dynamique active
document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
    loadCategories('categories-bar');
    document.getElementById('cart-overlay')?.addEventListener('click', closeCart);
    
    // Rendre la recherche active (masque les sections statiques et affiche résultats AJAX)
    const input = document.getElementById('search-input');
    if (input) {
        input.addEventListener('input', function() {
            const q = this.value.trim();
            const staticSections = document.querySelectorAll('.cat-section');
            const grid = document.getElementById('products-grid');

            if (q.length > 0) {
                staticSections.forEach(s => s.style.display = 'none');
                grid.style.display = 'grid';
                loadProducts('', q);
            } else {
                grid.style.display = 'none';
                staticSections.forEach(s => s.style.display = 'block');
            }
        });
    }

    // Masquer la grille AJAX par défaut
    const grid = document.getElementById('products-grid');
    if (grid) grid.style.display = 'none';
});

function logout() {
    const dataform = new FormData();
    dataform.append('action', 'deconnexion');
    fetch('back/auth_handler.php', { method: 'POST', body: dataform })
        .then(r => r.json())
        .then(d => { if(d.success) window.location.href = 'index.php'; });
}
</script>
</body>
</html>
