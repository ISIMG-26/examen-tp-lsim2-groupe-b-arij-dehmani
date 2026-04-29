
<?php
// Script pour hasher le mot de passe d'admin
require_once 'back/config.php';

$email_admin = 'admin@coffeshop.com';
$password = 'admin123'; // Le mot de passe d'admin

// Hacher le mot de passe
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Connexion à la base de données
$conn = getConnection();

// Mettre à jour le mot de passe dans la base de données
$stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email_admin);

if ($stmt->execute()) {
    echo "✅ Mot de passe hashé avec succès!<br>";
    echo "Email: " . htmlspecialchars($email_admin) . "<br>";
    echo "Hash: " . htmlspecialchars($hashed_password) . "<br><br>";
    echo "Tu peux maintenant te connecter avec:<br>";
    echo "Email: admin@coffeshop.com<br>";
    echo "Mot de passe: admin123<br><br>";
    echo "<a href='auth.php'>Aller à la page de connexion</a>";
} else {
    echo "❌ Erreur lors de la mise à jour: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
