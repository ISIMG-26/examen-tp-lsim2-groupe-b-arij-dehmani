<?php
// back/commande_handler.php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'passer_commande') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Vous devez être connecté.', 'redirect' => 'auth.php']);
        exit;
    }

    $panier = json_decode($_POST['panier'] ?? '[]', true);
    if (empty($panier)) {
        echo json_encode(['success' => false, 'message' => 'Panier vide.']);
        exit;
    }

    $conn = getConnection();
    $total = 0;

    // Calculer total & vérifier produits
    foreach ($panier as $item) {
        $stmt = $conn->prepare("SELECT prix FROM produits WHERE id = ? AND disponible = 1");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if (!$res) {
            echo json_encode(['success' => false, 'message' => 'Produit indisponible: ' . $item['nom']]);
            exit;
        }
        $total += $res['prix'] * $item['quantite'];
    }

    // Créer commande
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO commandes (utilisateur_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $commande_id = $conn->insert_id;

    // Insérer lignes
    foreach ($panier as $item) {
        $stmt = $conn->prepare("SELECT prix FROM produits WHERE id = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $prix = $stmt->get_result()->fetch_assoc()['prix'];

        $stmt = $conn->prepare("INSERT INTO lignes_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $commande_id, $item['id'], $item['quantite'], $prix);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Commande passée avec succès ! Merci ☕', 'commande_id' => $commande_id]);
    $conn->close();

} elseif ($action === 'mes_commandes') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Non connecté.']);
        exit;
    }
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT c.*, GROUP_CONCAT(p.nom ORDER BY p.nom SEPARATOR ', ') as produits_noms
        FROM commandes c 
        JOIN lignes_commande lc ON c.id = lc.commande_id
        JOIN produits p ON lc.produit_id = p.id
        WHERE c.utilisateur_id = ?
        GROUP BY c.id ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $commandes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'commandes' => $commandes]);
    $conn->close();
}
?>
