<?php
// back/auth_handler.php
// Endpoint AJAX unique pour inscription / connexion / déconnexion.

// Désactiver l'affichage des erreurs pour qu'elles n'interfèrent pas avec JSON
error_reporting(0);
ini_set('display_errors', 0);

// Définir le header JSON immédiatement
header('Content-Type: application/json; charset=utf-8');

// Maintenant inclure la config
require_once 'config.php';

$action = $_POST['action'] ?? '';

try {
    if ($action === 'inscription') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide']);
            exit;
        }

        if (strlen($password) < 6 || strlen($password) > 25) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir 6-25 caractères']);
            exit;
        }

        if ($password !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
            exit;
        }

        $conn = getConnection();

        // Vérifier si l'email existe
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email existe déjà']);
            exit;
        }

        // Hasher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insérer le nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'client')");
        $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed_password);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['prenom'] = $prenom;
            $_SESSION['nom'] = $nom;
            $_SESSION['role'] = 'client';
            echo json_encode(['success' => true, 'message' => 'Inscription réussie', 'redirect' => 'index.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
        }
        $conn->close();

    } elseif ($action === 'connexion') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
            exit;
        }

        $conn = getConnection();

        $stmt = $conn->prepare("SELECT id, prenom, nom, mot_de_passe, role FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['mot_de_passe'])) {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];

        $redirect = $user['role'] === 'admin' ? 'dashboard.php' : 'index.php';
        echo json_encode(['success' => true, 'message' => 'Connexion réussie', 'redirect' => $redirect]);
        $conn->close();

    } elseif ($action === 'deconnexion') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
