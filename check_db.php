<?php
require_once 'back/config.php';

echo "<h2>🔍 Vérification de la base de données</h2>";

$conn = getConnection();

echo "<h3>Produits:</h3>";
$result = $conn->query('SELECT COUNT(*) as count FROM produits');
$row = $result->fetch_assoc();
echo "<p>Nombre de produits: <strong>" . $row['count'] . "</strong></p>";

if ($row['count'] > 0) {
    echo "<h4>Derniers produits:</h4>";
    $result = $conn->query('SELECT id, nom, prix, disponible FROM produits ORDER BY id DESC LIMIT 5');
    while ($produit = $result->fetch_assoc()) {
        echo "<p>ID: {$produit['id']} | Nom: {$produit['nom']} | Prix: {$produit['prix']}€ | Dispo: {$produit['disponible']}</p>";
    }
}

echo "<h3>Catégories:</h3>";
$result = $conn->query('SELECT COUNT(*) as count FROM categories');
$row = $result->fetch_assoc();
echo "<p>Nombre de catégories: <strong>" . $row['count'] . "</strong></p>";

if ($row['count'] > 0) {
    echo "<h4>Catégories:</h4>";
    $result = $conn->query('SELECT id, nom FROM categories ORDER BY nom');
    while ($cat = $result->fetch_assoc()) {
        echo "<p>ID: {$cat['id']} | Nom: {$cat['nom']}</p>";
    }
}

$conn->close();
?>