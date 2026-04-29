<?php
// back/config.php - Connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'coffeeshop');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);//creation d un de connexion a la base de données oop constructeur de la classe mysqli
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Connexion échouée: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: ../auth.php');
        exit;
    }
}
?>
