<?php 
// back/auth_handler.php
// Endpoint AJAX unique pour inscription / connexion / déconnexion.
require_once 'config.php';
header('Content-Type: application/json'); // on change a json pour etre lu

$action = $_POST['action'] ?? ''; // recupere l action de la request post fu front

if ($action === 'inscription'){
    //pour recuperer les entrees de l utilisateur
    $nom=trim($_POST['nom'] ?? '');
    $prenom=trim($_POST['prenom'] ?? '');
    $email=trim($_POST['email'] ?? '');
    $password=$_POST['password'] ?? '';
    $confirm=$_POST['confirm']??'';

    // 2- validation backend(mm si existe validation front)
    if(empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm)){
        echo json_encode(['success'=>false,'message'=>'vous devez remplir tous les chaps ']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(['success'=>false,'message'=>'email non valide']);
        exit;
    }
    if(strlen($password)<6 || strlen($password)>25){
        echo json_encode(['success'=>false,'message'=>'le mot de passe doit contenir au moins 6 caracteres et au plus 25']);
        exit;
    }
    if($password !== $confirm){
        echo json_encode(['success'=>false,'message'=>'le mot de passe et la confirmation ne correspondent pas']);
        exit;
    }
    $conn=getConnection();

    //je dois verifier si l email existe deja
    $stmt= $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result= $stmt->get_result();
    if($result->num_rows > 0){
        echo json_encode(['success'=>false,'message'=>'Cet email existe déjà']);
        exit;
    }
    
    // Hasher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insérer le nouvel utilisateur
    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'client')");
    $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed_password);
    if($stmt->execute()) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['role'] = 'client';
        echo json_encode(['success' => true, 'message' => 'Inscription réussie.', 'redirect' => 'index.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription.']);
    }
    $conn->close();
}
elseif ($action === 'connexion'){
    // connexion AJAx
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => ' vous devez remplir tous les champs.']);
        exit;
    }
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id, prenom, nom, mot_de_passe, role FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'email ou mdp non valide.']);
        exit;
    }
    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => ' mdp incorrect.']);

        exit;
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['prenom'] = $user['prenom'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['role'] = $user['role'];


    $redirect = $user['role'] === 'admin' ? 'dashboard.php' : 'index.php';
    echo json_encode(['success' => true, 'message' => 'Connexion réussie.', 'redirect' => $redirect]);
    $conn->close();
}
elseif ($action === 'deconnexion') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Déconnexion réussie.']);
    exit;
}

?>
