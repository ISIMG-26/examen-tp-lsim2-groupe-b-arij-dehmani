CoffeeShop
Application web de commande en ligne pour un coffee shop, développée en PHP avec une interface dynamique en JavaScript.

c est une application web qui permet aux clients de parcourir un menu ,d ajouter des produits a une panier  et de passer une commande en ligne ..un dashboard pour l administrateur qui lui permet d ajouter ou de supprimer un produit

Fonctionnalités
----Côté client
  Navigation du menu : affichage des produits par catégorie avec filtres et recherche en temps réel
  Panier dynamique : ajout/supression du produit+mise  a jour de quantite
  commande : passage de commande avec recapitulatif
  authentification : inscription et connextion securise(bcrypt)
----Cote administrateur 
  Dashboard : vue d'ensemble (nombre de produits, utilisateurs, commandes)  
  Gestion des produits : ajout, suppression, activation/désactivation
  Images : support d'URL externe ou upload de fichier (jpg, jpeg, png, webp)

  coffeShop/
├── index.php               # Page principale — menu et panier
├── auth.php                # Page de connexion / inscription
├── dashboard.php           # Interface d'administration (admin uniquement)
├── categories.php          # Page des catégories
├── hash.php                # Utilitaire de hachage
│
├── back/
│   ├── config.php          # Configuration BDD + helpers de session
│   ├── auth_handler.php    # API : inscription, connexion, déconnexion
│   ├── produits_handler.php# API : gestion des produits et catégories
│   └── commande_handler.php# API : passage et consultation des commandes
│
├── css/
│   └── style.css           # Styles globaux de l'application
│
├── js/
│   ├── menu.js             # Logique du menu, panier et commande
│   ├── auth.js             # Logique des formulaires d'authentification
│   └── dashboard.js        # Logique du tableau de bord admin
│
├── database/
│   └── script.sql          # Script de création et initialisation de la BDD
│
└── images/                 # Dossier pour les images uploadées localement

Prérequis

PHP >= 7.4
MySQL >= 5.7 ou MariaDB >= 10.3
Serveur web : Apache
serbeur BDD : MySql


