<?php 

//ekhr point f ajax pour finalement passer la commande 
require_once 'db.php';
//format Json pour l front

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
if ($action === 'passer_commande'){

    if(!isLoggedin()){
        echo json_encode(['success'=>false,'message'=>'vous devez etre connecter pour passer une commande']);
        exit;
    }

    $panier = json_decode($_POST['panier'] ?? '[]', true); //puisque l panier tji en json depuis js je dois le transformer en rtableau pour le stocker dans php 
    if (empty($panier)){
        echo json_encode(['success'=>false,'message'=>'votre panier est vide']);
        exit;
    }

    $conn = getConnection();
    $total = 0;
   //nverifie la disponibilite du produit et je calcule le totale du panier 
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
    // je dois inserer la commande dans la bdd avec insert into 
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO commandes (utilisateur_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $commande_id = $conn->insert_id;

    // je dois inserer les details mtaa l commande fl bdd
    foreach ($panier as $item){
            $stmt = $conn->prepare("INSERT INTO details_commandes (commande_id, produit_id, quantite) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $commande_id, $item['id'], $item['quantite'], $prix);
            $stmt->execute();

    }
    echo json_encode(['success' => true, 'message' => 'Commande passée avec succès.', 'commande_id' => $commande_id]);
    $conn->close();
} elseif ($action === 'mes_commandes') {
    // Retourne l'historique des commandes de l'utilisateur connecté.
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