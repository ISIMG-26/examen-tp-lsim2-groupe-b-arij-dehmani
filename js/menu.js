/** index.php — panier + menu produits (AJAX) + déconnexion */

function esc(s) {
    const d = document.createElement('div');
    d.textContent = s ?? '';
    return d.innerHTML;
}

function logout() {
    const fd = new FormData();
    fd.append('action', 'deconnexion');
    fetch('back/auth_handler.php', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((d) => { if (d.success) location.href = 'index.php'; });
}

let cart = JSON.parse(localStorage.getItem('coffeecart') || '[]');
let activeCat = '';

function saveCart() {
    localStorage.setItem('coffeecart', JSON.stringify(cart));
    document.querySelectorAll('.cart-count').forEach((el) => {
        el.textContent = String(cart.reduce((n, i) => n + i.quantite, 0));
    });
    const box = document.getElementById('cart-items');
    if (!box) return;
    if (!cart.length) box.innerHTML = '<p>Panier vide</p>';
    else {
        box.innerHTML = cart.map((i) => `<div class="cart-item"><strong>${esc(i.nom)}</strong><div>
            <button type="button" class="qty-btn" onclick="q(${i.id},-1)">-</button> ${i.quantite}
            <button type="button" class="qty-btn" onclick="q(${i.id},1)">+</button></div></div>`).join('');
    }
    const t = document.getElementById('cart-total');
    if (t) t.textContent = (cart.reduce((s, i) => s + i.prix * i.quantite, 0)).toFixed(2) + ' €';
}

function q(id, delta) {
    const it = cart.find((x) => x.id === id);
    if (!it) return;
    it.quantite += delta;
    if (it.quantite <= 0) cart = cart.filter((x) => x.id !== id);
    saveCart();
}

function addToCart(id, nom, prix) {
    const it = cart.find((x) => x.id === id);
    if (it) it.quantite++;
    else cart.push({ id, nom, prix: parseFloat(prix), quantite: 1 });
    saveCart();
}

function openCart() {
    document.getElementById('cart-sidebar')?.classList.add('open');
    document.getElementById('cart-overlay')?.classList.add('open');
}

function closeCart() {
    document.getElementById('cart-sidebar')?.classList.remove('open');
    document.getElementById('cart-overlay')?.classList.remove('open');
}

async function passerCommande() {
    if (!cart.length) return alert('Panier vide.');
    const fd = new FormData();
    fd.append('action', 'passer_commande');
    fd.append('panier', JSON.stringify(cart));
    const data = await (await fetch('back/commande_handler.php', { method: 'POST', body: fd })).json();
    if (data.success) { cart = []; saveCart(); closeCart(); alert('Commande OK'); }
    else if (data.redirect) location.href = data.redirect;
    else alert(data.message || 'Erreur');
}

async function loadCats(barId) {
    const el = document.getElementById(barId);
    if (!el) return;
    const d = await (await fetch('back/produits_handler.php?action=get_categories')).json();
    if (!d.success) return;
    el.innerHTML = '<button type="button" class="cat-btn active" data-id="">Tous</button>' +
        d.categories.map((c) => `<button type="button" class="cat-btn" data-id="${c.id}">${esc(c.nom)}</button>`).join('');
    el.querySelectorAll('.cat-btn').forEach((b) => b.addEventListener('click', () => {
        el.querySelectorAll('.cat-btn').forEach((x) => x.classList.remove('active'));
        b.classList.add('active');
        activeCat = b.dataset.id || '';
        loadProds(activeCat, document.getElementById('search-input')?.value.trim() || '');
    }));
}

function imageSrc(p) {
    const img = (p.image || '').trim();
    if (!img) return '';
    if (img.startsWith('http')) return img;
    return 'images/' + img.replace(/^\/+/, '');
}

async function loadProds(cat = '', query = '') {
    const g = document.getElementById('products-grid');
    if (!g) return;
    let u = 'back/produits_handler.php?action=get_produits';
    if (cat) u += '&categorie_id=' + encodeURIComponent(cat);
    if (query) u += '&search=' + encodeURIComponent(query);
    const d = await (await fetch(u)).json();
    if (!d.success || !d.produits.length) { g.innerHTML = '<p>Aucun produit.</p>'; return; }

    g.innerHTML = d.produits.map((p) => {
        const src = imageSrc(p);
        const imgHtml = src
            ? `<div class="product-img-wrap"><img class="product-img" src="${esc(src)}" alt="${esc(p.nom)}" loading="lazy"></div>`
            : `<div class="product-img-wrap"><div class="product-img-fallback">☕</div></div>`;
        return `<div class="product-card">${imgHtml}<div class="product-body">
            <span class="product-cat-tag">${esc(p.categorie_nom || '')}</span>
            <div class="product-name">${esc(p.nom)}</div>
            <div class="product-desc">${esc(p.description || '')}</div>
            <div class="product-footer"><span class="product-price">${parseFloat(p.prix).toFixed(2)} €</span>
            <button type="button" class="add-to-cart-btn" onclick="addToCart(${p.id},'${esc(p.nom).replace(/'/g, "\\'")}',${p.prix})">+</button></div></div></div>`;
    }).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('cart-overlay')?.addEventListener('click', closeCart);
    saveCart();
    const inp = document.getElementById('search-input');
    if (inp) inp.addEventListener('input', () => loadProds(activeCat, inp.value.trim()));
    loadCats('categories-bar');
    loadProds();
});
