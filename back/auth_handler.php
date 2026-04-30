<?php
// JSON : inscription, connexion, déconnexion (appelé par fetch depuis le navigateur).
require_once 'config.php';
header('Content-Type: application/json');
$action = $_POST['action'] ?? '';

if ($action === 'inscription') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide.']);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit faire au moins 6 caractères.']);
        exit;
    }
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
        exit;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $prenom, $email, $hash);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès ! Vous pouvez vous connecter.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte.']);
    }
    $conn->close();

} elseif ($action === 'connexion') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis.']);
        exit;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, nom, prenom, mot_de_passe, role FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
        exit;
    }

    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['prenom'] = $user['prenom'];
    $_SESSION['role'] = $user['role'];

    $redirect = $user['role'] === 'admin' ? 'dashboard.php' : 'index.php';
    echo json_encode(['success' => true, 'message' => 'Connexion réussie !', 'redirect' => $redirect]);
    $conn->close();

} elseif ($action === 'deconnexion') {
    session_destroy();
    echo json_encode(['success' => true, 'redirect' => 'index.php']);

} else {
    echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
}
