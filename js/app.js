// js/app.js - Coffee Shop Main JavaScript

// ===== PANIER (Cart) =====
let cart = JSON.parse(localStorage.getItem('coffeecart') || '[]');

function saveCart() {
    localStorage.setItem('coffeecart', JSON.stringify(cart));
    updateCartUI();
}

function addToCart(id, nom, prix, icone = '☕') {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantite++;
    } else {
        cart.push({ id, nom, prix: parseFloat(prix), quantite: 1, icone });
    }
    saveCart();
    showToast('✓  ' + nom + ' ajouté au panier', 'success');
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.quantite += delta;
    if (item.quantite <= 0) removeFromCart(id);
    else saveCart();
}

function getCartTotal() {
    return cart.reduce((sum, item) => sum + item.prix * item.quantite, 0);
}

function getCartCount() {
    return cart.reduce((sum, item) => sum + item.quantite, 0);
}

function updateCartUI() {
    // Update count badges
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = getCartCount();
        el.style.display = getCartCount() > 0 ? 'inline-flex' : 'none';
    });

    // Update cart sidebar
    const itemsEl = document.getElementById('cart-items');
    const totalEl = document.getElementById('cart-total');
    if (!itemsEl) return;

    if (cart.length === 0) {
        itemsEl.innerHTML = `
            <div class="cart-empty">
                <div class="cart-empty-icon">☕</div>
                <p>Votre panier est vide</p>
                <p style="font-size:0.8rem;margin-top:0.5rem">Ajoutez vos boissons préférées</p>
            </div>`;
    } else {
        itemsEl.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-icon">${item.icone}</div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${escapeHtml(item.nom)}</div>
                    <div class="cart-item-price">${(item.prix * item.quantite).toFixed(2)} €</div>
                </div>
                <div class="cart-item-qty">
                    <button class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
                    <span class="qty-val">${item.quantite}</span>
                    <button class="qty-btn" onclick="updateQty(${item.id}, +1)">+</button>
                </div>
            </div>
        `).join('');
    }

    if (totalEl) totalEl.textContent = getCartTotal().toFixed(2) + ' €';
}

// ===== CART SIDEBAR TOGGLE =====
function openCart() {
    document.getElementById('cart-sidebar')?.classList.add('open');
    document.getElementById('cart-overlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    document.getElementById('cart-sidebar')?.classList.remove('open');
    document.getElementById('cart-overlay')?.classList.remove('open');
    document.body.style.overflow = '';
}

// ===== PASSER COMMANDE =====
async function passerCommande() {
    if (cart.length === 0) {
        showToast('Votre panier est vide', 'error');
        return;
    }

    const btn = document.getElementById('checkout-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Traitement...'; }

    try {
        const formData = new FormData();
        formData.append('action', 'passer_commande');
        formData.append('panier', JSON.stringify(cart));

        const res = await fetch('back/commande_handler.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            cart = [];
            saveCart();
            closeCart();
            showToast('🎉 Commande confirmée ! Merci pour votre confiance.', 'success');
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showToast(data.message, 'error');
        }
    } catch (e) {
        showToast('Erreur réseau. Veuillez réessayer.', 'error');
    }

    if (btn) { btn.disabled = false; btn.textContent = 'Commander'; }
}

// ===== TOAST =====
function showToast(msg, type = 'success') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'toast';
        toast.innerHTML = '<span class="toast-icon"></span><span class="toast-msg"></span>';
        document.body.appendChild(toast);
    }
    toast.className = 'toast ' + type;
    toast.querySelector('.toast-msg').textContent = msg;
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => toast.classList.remove('show'), 3500);
}

// ===== ESCAPE HTML =====
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ===== SEARCH (AJAX) =====
let searchTimeout;
function setupSearch() {
    const input = document.getElementById('search-input');
    if (!input) return;

    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = this.value.trim();
            const activeCat = document.querySelector('.cat-btn.active')?.dataset.id || '';
            loadProducts(activeCat, query);
        }, 350);
    });
}

// ===== LOAD PRODUCTS (AJAX) =====
async function loadProducts(categorieId = '', search = '') {
    const grid = document.getElementById('products-grid');
    if (!grid) return;

    grid.innerHTML = '<div class="loading"><div class="spinner"></div> Chargement...</div>';

    let url = 'back/produits_handler.php?action=get_produits';
    if (categorieId) url += '&categorie_id=' + encodeURIComponent(categorieId);
    if (search) url += '&search=' + encodeURIComponent(search);

    try {
        const res = await fetch(url);
        const data = await res.json();

        if (!data.success || data.produits.length === 0) {
            grid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--mocha);opacity:0.7">
                    <div style="font-size:3rem;margin-bottom:1rem">☕</div>
                    <p>Aucun produit trouvé</p>
                </div>`;
            return;
        }

        // Icônes par catégorie
        const icons = { 'Cafés Chauds': '☕', 'Boissons Froides': '🧋', 'Pâtisseries': '🥐', 'Sandwichs': '🥪', 'Thés & Infusions': '🍵' };

        grid.innerHTML = data.produits.map(p => {
            const icone = icons[p.categorie_nom] || '☕';
            const imgSrc = p.image || '';
            return `
                <div class="product-card" data-id="${p.id}">
                    <div class="product-img-wrap">
                        <img class="product-img"
                             src="${escapeHtml(imgSrc)}"
                             alt="${escapeHtml(p.nom)}"
                             onerror="this.parentElement.innerHTML='<div class=\'product-img-fallback\'>${icone}</div>'">
                    </div>
                    <div class="product-body">
                        <span class="product-cat-tag">${escapeHtml(p.categorie_nom)}</span>
                        <div class="product-name">${escapeHtml(p.nom)}</div>
                        <div class="product-desc">${escapeHtml(p.description || '')}</div>
                        <div class="product-footer">
                            <span class="product-price">${parseFloat(p.prix).toFixed(2)} €</span>
                            <button class="add-to-cart-btn" 
                                onclick="addToCart(${p.id}, '${escapeHtml(p.nom).replace(/'/g,"\\'")}', ${p.prix}, '${icone}')"
                                title="Ajouter au panier">+</button>
                        </div>
                    </div>
                </div>`;
        }).join('');
    } catch (e) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--accent)">Erreur de chargement. Réessayez.</div>';
    }
}

// ===== LOAD CATEGORIES (AJAX) =====
async function loadCategories(containerId, callback) {
    try {
        const res = await fetch('back/produits_handler.php?action=get_categories');
        const data = await res.json();
        const container = document.getElementById(containerId);
        if (!container || !data.success) return;

        container.innerHTML = `
            <button class="cat-btn active" data-id="" onclick="filterCategory(this, '')">Tout voir</button>
            ${data.categories.map(c => `
                <button class="cat-btn" data-id="${c.id}" onclick="filterCategory(this, '${c.id}')">
                    ${c.icone} ${escapeHtml(c.nom)}
                </button>`).join('')}
        `;
        if (callback) callback(data.categories);
    } catch (e) {}
}

function filterCategory(btn, id) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const search = document.getElementById('search-input')?.value || '';
    loadProducts(id, search);
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
    setupSearch();

    // Cart events
    document.getElementById('cart-overlay')?.addEventListener('click', closeCart);

    // Load products if grid present
    if (document.getElementById('products-grid')) {
        loadCategories('categories-bar');
        loadProducts();
    }
});
