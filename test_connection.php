<?php
// Test de connexion et vérification des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test de Connexion</h2>";

require_once 'back/config.php';

echo "<h3>1️⃣ Test de la base de données</h3>";
try {
    $conn = getConnection();
    echo "<p>✅ Connexion réussie</p>";
    
    // Vérifier les tables
    $tables = ['utilisateurs', 'commandes', 'produits', 'categories'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p>✅ Table '$table' existe</p>";
        } else {
            echo "<p>❌ Table '$table' n'existe pas</p>";
        }
    }
    
    // Vérifier les utilisateurs
    echo "<h3>2️⃣ Utilisateurs dans la base</h3>";
    $result = $conn->query("SELECT id, email, mot_de_passe, role FROM utilisateurs");
    if ($result && $result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            echo "<p>Email: <strong>" . htmlspecialchars($user['email']) . "</strong> | Rôle: <strong>" . htmlspecialchars($user['role']) . "</strong></p>";
            echo "<p>Hash: " . htmlspecialchars(substr($user['mot_de_passe'], 0, 30)) . "...</p>";
            echo "<hr>";
        }
    } else {
        echo "<p>❌ Aucun utilisateur trouvé!</p>";
    }
    
    // Test password_hash et password_verify
    echo "<h3>3️⃣ Test password_hash / password_verify</h3>";
    $test_pwd = 'admin123';
    $test_hash = password_hash($test_pwd, PASSWORD_DEFAULT);
    echo "<p>Hash test: " . htmlspecialchars($test_hash) . "</p>";
    
    if (password_verify($test_pwd, $test_hash)) {
        echo "<p>✅ password_verify fonctionne!</p>";
    } else {
        echo "<p>❌ password_verify échoue!</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<hr>
<p><a href="hash_admin_password.php">Aller au script de reset du mot de passe</a></p>
