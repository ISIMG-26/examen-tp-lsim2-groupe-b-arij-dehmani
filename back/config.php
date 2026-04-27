<?php 
// Configuration database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'coffeeshop');
function connection(){
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); //instance  : creation d un de connexion a la base de données oop constructeur de la classe mysqli
    if ($conn->connect_error) {
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error])); //pour la convertie en objet json 
    }
    return $conn;
};
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Démarrer la session si elle n'est pas déjà démarrée bhs tkhazen l user khatr m kensh mawjoud 
}
function isAdmin(){
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; //determine si le variable est declare admin et n  est pas null et return true
}
function isLoggedIn(){
    return isset($_SESSION['user_id']); //determine si le variable est declare et n  est pas null 
}
function redirect(){
    if (!isAdmin()) {
        header('Location: ../auth.php');
        exit;
    }
}
?>
