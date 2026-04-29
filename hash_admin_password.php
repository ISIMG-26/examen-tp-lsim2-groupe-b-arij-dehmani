
<?php
// Script pour hasher le mot de passe d'admin
require_once 'back/config.php';

echo "<h2>🔧 Script de Réinitialisation du Mot de Passe Admin</h2>";

$email_admin = 'admin@coffeshop.com';
$password = 'admin123'; // Le mot de passe d'admin

// Hacher le mot de passe
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "<p>✅ Mot de passe hashé: " . htmlspecialchars($hashed_password) . "</p>";

// Connexion à la base de données
$conn = getConnection();
echo "<p>✅ Connecté à la base de données</p>";

// Vérifier si l'utilisateur admin existe
$stmt = $conn->prepare("SELECT id, email, mot_de_passe FROM utilisateurs WHERE email = ?");
$stmt->bind_param("s", $email_admin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>⚠️ Admin n'existe pas, création...</p>";
    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'admin')");
    $nom = 'admin';
    $prenom = 'admin';
    $stmt->bind_param("ssss", $nom, $prenom, $email_admin, $hashed_password);
    if ($stmt->execute()) {
        echo "<p>✅ Admin créé avec succès!</p>";
    } else {
        echo "<p>❌ Erreur création admin: " . $stmt->error . "</p>";
    }
} else {
    echo "<p>✅ Admin existe, mise à jour du mot de passe...</p>";
    $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email_admin);
    if ($stmt->execute()) {
        echo "<p>✅ Mot de passe mis à jour!</p>";
    } else {
        echo "<p>❌ Erreur update: " . $stmt->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>📋 Données actuelles:</h3>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($email_admin) . "</p>";
echo "<p><strong>Mot de passe:</strong> admin123</p>";
echo "<p><strong>Hash dans DB:</strong> " . htmlspecialchars($hashed_password) . "</p>";

// Tester password_verify
if (password_verify($password, $hashed_password)) {
    echo "<p>✅ Vérification: Le mot de passe est correct!</p>";
} else {
    echo "<p>❌ Erreur: password_verify échoue!</p>";
}

echo "<hr>";
echo "<p><a href='auth.php' style='padding: 10px; background: blue; color: white; text-decoration: none;'>Aller à la connexion</a></p>";

$stmt->close();
$conn->close();
?>

