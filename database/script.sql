-- Base de données CoffeeShop
CREATE DATABASE IF NOT EXISTS coffeeshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE coffeeshop;

-- Table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    icone VARCHAR(10) DEFAULT '☕',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des produits
CREATE TABLE IF NOT EXISTS produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    prix DECIMAL(8,2) NOT NULL,
    image VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=80',
    categorie_id INT NOT NULL,
    stock INT DEFAULT 100,
    disponible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('client','admin') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des commandes
CREATE TABLE IF NOT EXISTS commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente','confirmee','livree','annulee') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Table des lignes de commande
CREATE TABLE IF NOT EXISTS lignes_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

-- Données de test : catégories
INSERT INTO categories (nom, description, icone) VALUES
('Cafés Chauds', 'Espresso, cappuccino, latte et plus', '☕'),
('Boissons Froides', 'Frappés, cold brew, smoothies', '🧋'),
('Pâtisseries', 'Croissants, muffins, gâteaux maison', '🥐'),
('Sandwichs', 'Sandwichs et paninis frais du jour', '🥪'),
('Thés & Infusions', 'Sélection de thés premium', '🍵');

-- Données de test : produits (avec images Unsplash)
INSERT INTO produits (nom, description, prix, categorie_id, image) VALUES
('Espresso', 'Café intense et pur, extraction parfaite', 2.50, 1, 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=400&q=80'),
('Cappuccino', 'Espresso avec mousse de lait crémeuse', 3.80, 1, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&q=80'),
('Latte Caramel', 'Latte doux avec sirop caramel maison', 4.50, 1, 'https://images.unsplash.com/photo-1561882468-9110d70a79cd?w=400&q=80'),
('Café Noisette', 'Espresso allongé avec une touche de lait', 3.20, 1, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&q=80'),
('Cold Brew', 'Café infusé à froid pendant 12h', 4.80, 2, 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=400&q=80'),
('Frappé Vanille', 'Café glacé mixé avec crème vanille', 5.50, 2, 'https://images.unsplash.com/photo-1572490122747-3e9ac5d91c6e?w=400&q=80'),
('Matcha Latte Froid', 'Matcha japonais premium sur glace', 5.20, 2, 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?w=400&q=80'),
('Croissant Beurre', 'Croissant feuilleté fait maison chaque matin', 2.80, 3, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&q=80'),
('Muffin Myrtilles', 'Muffin moelleux aux myrtilles fraîches', 3.20, 3, 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=400&q=80'),
('Cheesecake', 'Cheesecake new-yorkais onctueux', 4.50, 3, 'https://images.unsplash.com/photo-1578775887804-699de7086ff9?w=400&q=80'),
('Sandwich Club', 'Poulet, avocat, tomates, roquette', 6.80, 4, 'https://images.unsplash.com/photo-1553909489-cd47e0907980?w=400&q=80'),
('Panini Mozzarella', 'Mozzarella, tomates séchées, pesto', 6.20, 4, 'https://images.unsplash.com/photo-1528736235302-52922df5c122?w=400&q=80'),
('Earl Grey', 'Thé noir bergamote de qualité supérieure', 3.00, 5, 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400&q=80'),
('Chai Latte', 'Mélange  épices indiennes avec lait chaud', 4.20, 5, 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=400&q=80');

-- Admin par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Coffee', 'admin@coffeeshop.com', 'admin123', 'admin');
