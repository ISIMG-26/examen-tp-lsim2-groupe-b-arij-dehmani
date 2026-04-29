<?php
require_once 'back/config.php';
redirectIfNotAdmin();

$conn = getConnection();
$nb_produits = $conn->query("SELECT COUNT(*) FROM produits")->fetch_row()[0];
$nb_categories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$nb_users = $conn->query("SELECT COUNT(*) FROM utilisateurs WHERE role='client'")->fetch_row()[0];
$nb_commandes = $conn->query("SELECT COUNT(*) FROM commandes")->fetch_row()[0];
$categories = $conn->query("SELECT * FROM categories ORDER BY nom")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Brew & Co</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav>
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">Brew<span>&</span>Co</a>
        <ul class="nav-links">
            <li><a href="index.php">🏠 Accueil</a></li>
            <li><a href="categories.php">Menu</a></li>
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="#" onclick="logout()">🚪 Déconnexion</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div style="padding:1.5rem;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:1rem">
            <div style="font-family:'Playfair Display',serif;color:var(--latte);font-size:1rem">
                👑 <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
            </div>
            <div style="color:var(--caramel);font-size:0.75rem;margin-top:0.2rem">Administrateur</div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-label">Navigation</div>
            <a href="#section-stats" class="sidebar-link active">📊 Vue d'ensemble</a>
            <a href="#section-products" class="sidebar-link" onclick="showSection('products')">☕ Produits</a>
            <a href="#section-orders" class="sidebar-link" onclick="showSection('orders')">📋 Commandes</a>
            <a href="#section-categories" class="sidebar-link" onclick="showSection('categories')">🗂 Catégories</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-label">Actions rapides</div>
            <a href="#" class="sidebar-link" onclick="openAddProductModal()">➕ Nouveau produit</a>
            <a href="index.php" class="sidebar-link">🌐 Voir le site</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main">

        <!-- STATS -->
        <section id="section-stats">
            <div class="dashboard-header">
                <h1 class="section-title">Tableau de <span>Bord</span></h1>
                <span style="color:var(--mocha);font-size:0.88rem">Bienvenue, <?= htmlspecialchars($_SESSION['prenom']) ?> ☕</span>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orange">☕</div>
                    <div>
                        <div class="stat-val"><?= $nb_produits ?></div>
                        <div class="stat-label">Produits</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon dark">🗂</div>
                    <div>
                        <div class="stat-val"><?= $nb_categories ?></div>
                        <div class="stat-label">Catégories</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">👥</div>
                    <div>
                        <div class="stat-val"><?= $nb_users ?></div>
                        <div class="stat-label">Clients</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">📋</div>
                    <div>
                        <div class="stat-val"><?= $nb_commandes ?></div>
                        <div class="stat-label">Commandes</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRODUCTS SECTION -->
        <section id="section-products" style="margin-top:2rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
                <h2 class="section-title" style="font-size:1.6rem">Gestion des <span>Produits</span></h2>
                <button class="btn-primary" onclick="openAddProductModal()">+ Ajouter</button>
            </div>

            <!-- Filtre catégorie -->
            <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-bottom:1.2rem">
                <button class="cat-btn active" onclick="loadAdminProducts('', this)">Tous</button>
                <?php foreach($categories as $c): ?>
                <button class="cat-btn" onclick="loadAdminProducts('<?= $c['id'] ?>', this)"><?= htmlspecialchars($c['nom']) ?></button>
                <?php endforeach; ?>
            </div>

            <div id="admin-products-table">
                <div class="loading"><div class="spinner"></div> Chargement...</div>
            </div>
        </section>

        <!-- ORDERS SECTION -->
        <section id="section-orders" style="margin-top:3rem">
            <h2 class="section-title" style="font-size:1.6rem;margin-bottom:1.5rem">
                Dernières <span>Commandes</span>
            </h2>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Produits</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $commandes = $conn->query("
                        SELECT c.id, c.total, c.statut, c.created_at,
                               u.prenom, u.nom,
                               GROUP_CONCAT(p.nom SEPARATOR ', ') as produits
                        FROM commandes c
                        JOIN utilisateurs u ON c.utilisateur_id = u.id
                        JOIN lignes_commande lc ON c.id = lc.commande_id
                        JOIN produits p ON lc.produit_id = p.id
                        GROUP BY c.id
                        ORDER BY c.created_at DESC LIMIT 20
                    ")->fetch_all(MYSQLI_ASSOC);

                    if (empty($commandes)):
                    ?>
                    <tr><td colspan="6" style="text-align:center;opacity:0.6;padding:2rem">Aucune commande pour le moment</td></tr>
                    <?php else: foreach ($commandes as $cmd):
                        $badge = match($cmd['statut']) {
                            'confirmee' => 'badge-green',
                            'livree' => 'badge-green',
                            'annulee' => 'badge-red',
                            default => 'badge-orange'
                        };
                    ?>
                    <tr>
                        <td><strong>#<?= $cmd['id'] ?></strong></td>
                        <td><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($cmd['produits']) ?></td>
                        <td><strong><?= number_format($cmd['total'], 2) ?> €</strong></td>
                        <td><span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_',' ',$cmd['statut'])) ?></span></td>
                        <td style="color:var(--mocha);font-size:0.85rem"><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- CATEGORIES SECTION -->
        <section id="section-categories" style="margin-top:3rem">
            <h2 class="section-title" style="font-size:1.6rem;margin-bottom:1.5rem">
                Gestion des <span>Catégories</span>
            </h2>
            <div style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Icône</th><th>Nom</th><th>Description</th><th>Nb Produits</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach($categories as $c):
                        $count = $conn->query("SELECT COUNT(*) FROM produits WHERE categorie_id={$c['id']}")->fetch_row()[0];
                    ?>
                    <tr>
                        <td style="font-size:1.5rem"><?= $c['icone'] ?></td>
                        <td><strong><?= htmlspecialchars($c['nom']) ?></strong></td>
                        <td style="color:var(--mocha)"><?= htmlspecialchars($c['description'] ?? '') ?></td>
                        <td><span class="badge badge-green"><?= $count ?> produits</span></td>
                    </tr>
                    <?php endforeach; $conn->close(); ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<!-- MODAL AJOUTER PRODUIT -->
<div class="modal-overlay" id="add-product-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">☕ Nouveau Produit</h2>
            <button class="cart-close" onclick="closeModal()">✕</button>
        </div>

        <div class="alert" id="modal-alert"></div>

        <div class="form-group">
            <label class="form-label">Nom du produit *</label>
            <input type="text" id="prod-nom" class="form-input" placeholder="Ex: Latte Caramel">
            <div class="form-error" id="err-prod-nom">Nom requis</div>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea id="prod-desc" class="form-input" rows="3" placeholder="Décrivez votre produit..." style="resize:vertical"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Prix (€) *</label>
                <input type="number" id="prod-prix" class="form-input" placeholder="4.50" step="0.10" min="0">
                <div class="form-error" id="err-prod-prix">Prix invalide</div>
            </div>
            <div class="form-group">
                <label class="form-label">Stock</label>
                <input type="number" id="prod-stock" class="form-input" placeholder="100" min="0" value="100">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">URL de l'image</label>
            <input type="url" id="prod-image" class="form-input" 
                   placeholder="https://images.unsplash.com/..."
                   oninput="previewImage(this.value)">
            <div id="img-preview" style="margin-top:0.8rem;display:none">
                <img id="img-preview-el" src="" alt="Aperçu"
                     style="width:100%;height:140px;object-fit:cover;border-radius:10px;border:2px solid var(--cream)">
            </div>
            <p style="font-size:0.75rem;color:var(--mocha);margin-top:0.4rem;opacity:0.7">
                💡 Astuce : utilise <a href="https://unsplash.com" target="_blank" style="color:var(--caramel)">unsplash.com</a> pour trouver des images gratuites
            </p>
        </div>
        <div class="form-group">
            <label class="form-label">Catégorie *</label>
            <select id="prod-cat" class="form-input">
                <option value="">Choisir une catégorie</option>
                <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-error" id="err-prod-cat">Catégorie requise</div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button class="btn-secondary" onclick="closeModal()" style="flex:1">Annuler</button>
            <button class="btn-primary" onclick="submitProduct()" style="flex:2" id="btn-add-prod">
                ✓ Ajouter le produit
            </button>
        </div>
    </div>
</div>

<script>
// Load admin products table
async function loadAdminProducts(catId = '', btn = null) {
    if (btn) {
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    const container = document.getElementById('admin-products-table');
    container.innerHTML = '<div class="loading"><div class="spinner"></div> Chargement...</div>';

    let url = 'back/produits_handler.php?action=get_all_admin';
    if (catId) url += '&cat=' + catId;

    const res = await fetch(url);
    const data = await res.json();

    if (!data.produits || data.produits.length === 0) {
        container.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--mocha);opacity:0.7">Aucun produit</p>';
        return;
    }

    const filtered = catId ? data.produits.filter(p => p.categorie_id == catId) : data.produits;

    container.innerHTML = `
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead><tr>
                <th>Image</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            ${filtered.map(p => `
                <tr>
                    <td>
                        <img src="${escHtml(p.image || '')}" alt="${escHtml(p.nom)}"
                             style="width:55px;height:45px;object-fit:cover;border-radius:8px;background:var(--cream)"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                        >
                        <div style="display:none;width:55px;height:45px;background:linear-gradient(135deg,var(--roast),var(--mocha));border-radius:8px;align-items:center;justify-content:center;font-size:1.4rem">☕</div>
                    </td>
                    <td><strong>${escHtml(p.nom)}</strong><br><span style="font-size:0.8rem;color:var(--mocha);opacity:0.8">${escHtml(p.description?.substring(0,50) || '')}...</span></td>
                    <td>${escHtml(p.categorie_nom)}</td>
                    <td><strong style="color:var(--caramel)">${parseFloat(p.prix).toFixed(2)} €</strong></td>
                    <td>${p.stock}</td>
                    <td>
                        <span class="badge ${p.disponible == 1 ? 'badge-green' : 'badge-red'}">
                            ${p.disponible == 1 ? 'Disponible' : 'Indisponible'}
                        </span>
                    </td>
                    <td>
                        <button onclick="toggleDispo(${p.id}, ${p.disponible})" 
                            style="background:var(--cream);border:none;padding:0.3rem 0.7rem;border-radius:6px;cursor:pointer;font-size:0.8rem;margin-right:4px">
                            ${p.disponible == 1 ? '🔴 Désactiver' : '🟢 Activer'}
                        </button>
                        <button onclick="deleteProduct(${p.id}, '${escHtml(p.nom).replace(/'/g,"\\'")}' )"
                            style="background:#f8d7da;border:none;padding:0.3rem 0.7rem;border-radius:6px;cursor:pointer;font-size:0.8rem;color:#721c24">
                            🗑
                        </button>
                    </td>
                </tr>
            `).join('')}
            </tbody>
        </table>
        </div>`;
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function toggleDispo(id, current) {
    const fd = new FormData();
    fd.append('action', 'modifier_dispo');
    fd.append('id', id);
    fd.append('disponible', current == 1 ? 0 : 1);
    await fetch('back/produits_handler.php', { method: 'POST', body: fd });
    loadAdminProducts();
}

async function deleteProduct(id, nom) {
    if (!confirm(`Supprimer "${nom}" ?`)) return;
    const fd = new FormData();
    fd.append('action', 'supprimer');
    fd.append('id', id);
    const res = await fetch('back/produits_handler.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) { showModalAlert('Produit supprimé', 'success'); loadAdminProducts(); }
    else alert(data.message);
}

// Modal
function openAddProductModal() {
    document.getElementById('add-product-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('add-product-modal').classList.remove('open');
    document.body.style.overflow = '';
    document.getElementById('modal-alert').className = 'alert';
    clearFormErrors();
}

function showModalAlert(msg, type) {
    const el = document.getElementById('modal-alert');
    el.textContent = msg;
    el.className = 'alert show ' + type;
}

function clearFormErrors() {
    document.querySelectorAll('.form-error').forEach(e => e.classList.remove('show'));
}

function previewImage(url) {
    const preview = document.getElementById('img-preview');
    const img = document.getElementById('img-preview-el');
    if (url && url.startsWith('http')) {
        img.src = url;
        preview.style.display = 'block';
        img.onerror = () => { preview.style.display = 'none'; };
    } else {
        preview.style.display = 'none';
    }
}

async function submitProduct() {
    clearFormErrors();
    const nom = document.getElementById('prod-nom').value.trim();
    const prix = parseFloat(document.getElementById('prod-prix').value);
    const cat = document.getElementById('prod-cat').value;
    let valid = true;

    if (!nom) { document.getElementById('err-prod-nom').classList.add('show'); valid = false; }
    if (!prix || prix <= 0) { document.getElementById('err-prod-prix').classList.add('show'); valid = false; }
    if (!cat) { document.getElementById('err-prod-cat').classList.add('show'); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('btn-add-prod');
    btn.disabled = true; btn.textContent = 'Ajout en cours...';

    const fd = new FormData();
    fd.append('action', 'ajouter');
    fd.append('nom', nom);
    fd.append('description', document.getElementById('prod-desc').value.trim());
    fd.append('prix', prix);
    fd.append('stock', document.getElementById('prod-stock').value || 100);
    fd.append('categorie_id', cat);
    fd.append('image_url', document.getElementById('prod-image').value.trim());

    const res = await fetch('back/produits_handler.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
        showModalAlert('✓ ' + data.message, 'success');
        ['prod-nom','prod-desc','prod-prix','prod-stock','prod-image'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('prod-cat').value = '';
        document.getElementById('img-preview').style.display = 'none';
        loadAdminProducts();
        setTimeout(closeModal, 1500);
    } else {
        showModalAlert(data.message, 'error');
    }
    btn.disabled = false; btn.textContent = '✓ Ajouter le produit';
}

// Logout
function logout() {
    const fd = new FormData();
    fd.append('action', 'deconnexion');
    fetch('back/auth_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if(d.success) window.location.href = 'index.php'; });
}

// Close modal on overlay click
document.getElementById('add-product-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Init
document.addEventListener('DOMContentLoaded', () => loadAdminProducts());
</script>
</body>
</html>
