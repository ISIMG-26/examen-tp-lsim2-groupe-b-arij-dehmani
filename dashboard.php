<?php
require_once 'back/config.php';
redirectIfNotAdmin();
$conn = getConnection();
$nb_produits = $conn->query("SELECT COUNT(*) FROM produits")->fetch_row()[0];
$nb_commandes = $conn->query("SELECT COUNT(*) FROM commandes")->fetch_row()[0];
$nb_users = $conn->query("SELECT COUNT(*) FROM utilisateurs")->fetch_row()[0];
$categories = $conn->query("SELECT id, nom FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav>
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">CoffeeShop</a>
        <ul class="nav-links">
            <li><a href="index.php">Menu</a></li>
            <li><a href="dashboard.php" class="active">Admin</a></li>
            <li><a href="#" onclick="logout();return false;">Logout</a></li>
        </ul>
    </div>
</nav>

<main class="section">
    <h2>Dashboard léger</h2>
    <p>Produits: <strong><?= $nb_produits ?></strong> | Utilisateurs: <strong><?= $nb_users ?></strong> | Commandes: <strong><?= $nb_commandes ?></strong></p>

    <h3 style="margin-top:1.5rem">Ajouter un produit (minimal)</h3>
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 2fr 2fr auto;gap:10px;align-items:center">
        <input id="p-nom" class="form-input" type="text" placeholder="Nom produit">
        <input id="p-prix" class="form-input" type="number" step="0.01" min="0" placeholder="Prix">
        <select id="p-cat" class="form-input">
            <option value="">Catégorie</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <input id="p-image-url" class="form-input" type="url" placeholder="Image URL (optionnel)">
        <input id="p-image-file" class="form-input" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <button type="button" class="btn-primary" onclick="dashAdd()">Ajouter</button>
    </div>

    <h3 style="margin-top:1.5rem">Produits</h3>
    <div id="admin-products">Chargement...</div>
</main>
<script src="js/dashboard.js"></script>
</body>
</html>
