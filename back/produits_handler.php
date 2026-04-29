<?php
// back/produits_handler.php
// Endpoint AJAX produits/catégories (public) + actions admin (protégées).
require_once 'config.php';

// Réponse JSON standard pour les appels fetch().
header('Content-Type: application/json');

// L'action peut venir en GET (lecture) ou POST (écriture).
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// -------- ACTIONS PUBLIQUES (GET) --------
// Retourne les produits visibles avec filtre catégorie + recherche texte.
if ($action === 'get_produits') {
    $conn = getConnection();
    $categorie_id = $_GET['categorie_id'] ?? null;
    $search = $_GET['search'] ?? '';

    $sql = "SELECT p.*, c.nom AS categorie_nom FROM produits p 
            JOIN categories c ON p.categorie_id = c.id WHERE p.disponible = 1";
    $params = [];
    $types = "";

    if ($categorie_id) {
        $sql .= " AND p.categorie_id = ?";
        $types .= "i";
        $params[] = $categorie_id;
    }
    if ($search) {
        $sql .= " AND (p.nom LIKE ? OR p.description LIKE ?)";
        $types .= "ss";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) {
        // Liaison dynamique des paramètres pour garder la requête sécurisée.
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $produits = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'produits' => $produits]);
    $conn->close();
    exit;
}

// Retourne toutes les catégories pour construire les filtres côté UI.
if ($action === 'get_categories') {
    $conn = getConnection();
    $result = $conn->query("SELECT * FROM categories ORDER BY nom");
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'categories' => $categories]);
    $conn->close();
    exit;
}

// -------- ACTIONS ADMIN (POST/GET) --------
// Tout ce qui modifie le catalogue est restreint à l'admin.
redirectIfNotAdmin();

if ($action === 'ajouter') {
    // Crée un produit depuis le formulaire admin.
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 100);

    if (empty($nom) || $prix <= 0 || $categorie_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants.']);
        exit;
    }

    // Gestion image avec priorité:
    // 1) URL saisie par admin
    // 2) fichier uploadé
    // 3) image par défaut
    $image = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=80'; // défaut
    
    // Priorité 1 : URL fournie
    if (!empty($_POST['image_url']) && filter_var($_POST['image_url'], FILTER_VALIDATE_URL)) {
        $image = trim($_POST['image_url']);
    }
    // Priorité 2 : fichier uploadé
    elseif (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array(strtolower($ext), $allowed)) {
            // Nom unique pour éviter collisions de fichiers.
            $image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../images/' . $image);
        }
    }

    // Insertion produit en DB.
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO produits (nom, description, prix, categorie_id, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiis", $nom, $description, $prix, $categorie_id, $stock, $image);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Produit ajouté avec succès !']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout.']);
    }
    $conn->close();

} elseif ($action === 'supprimer') {
    // Supprime un produit par id.
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'message' => 'ID invalide.']); exit; }

    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Produit supprimé.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
    }
    $conn->close();

} elseif ($action === 'modifier_dispo') {
    // Active/Désactive la visibilité produit dans le menu client.
    $id = intval($_POST['id'] ?? 0);
    $dispo = intval($_POST['disponible'] ?? 1);
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE produits SET disponible = ? WHERE id = ?");
    $stmt->bind_param("ii", $dispo, $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    $conn->close();

} elseif ($action === 'get_all_admin') {
    // Liste complète pour la table admin.
    $conn = getConnection();
    $result = $conn->query("SELECT p.*, c.nom AS categorie_nom FROM produits p JOIN categories c ON p.categorie_id = c.id ORDER BY p.created_at DESC");
    $produits = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'produits' => $produits]);
    $conn->close();
}
?>
