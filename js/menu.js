// Panier (localStorage), panneau latéral, commande, déconnexion.
const CART_KEY = 'kah_ena_cart';

function loadCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
  } catch {
    return [];
  }
}

function saveCart(items) {
  localStorage.setItem(CART_KEY, JSON.stringify(items));
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

function addToCart(product) {
  const cart = loadCart();
  const id = Number(product.id);
  const i = cart.findIndex((x) => Number(x.id) === id);
  if (i >= 0) cart[i].quantite += 1;
  else cart.push({ id, nom: product.nom, prix: Number(product.prix), quantite: 1 });
  saveCart(cart);
  renderCart();
  updateCartCount();
}

function changeQty(index, delta) {
  const cart = loadCart();
  if (!cart[index]) return;
  cart[index].quantite += delta;
  if (cart[index].quantite <= 0) cart.splice(index, 1);
  saveCart(cart);
  renderCart();
  updateCartCount();
}

function updateCartCount() {
  const n = loadCart().reduce((s, x) => s + x.quantite, 0);
  document.querySelectorAll('.cart-count').forEach((el) => {
    el.textContent = n;
  });
}

function renderCart() {
  const box = document.getElementById('cart-items');
  const totalEl = document.getElementById('cart-total');
  if (!box || !totalEl) return;
  const cart = loadCart();
  if (!cart.length) {
    box.innerHTML = '<p style="padding:8px;color:#666">Panier vide.</p>';
    totalEl.textContent = '0.00 €';
    return;
  }
  let total = 0;
  box.innerHTML = cart
    .map((item, idx) => {
      const line = item.prix * item.quantite;
      total += line;
      return `<div class="cart-item">
        <div>
          <div style="font-weight:700">${esc(item.nom)}</div>
          <div style="font-size:.85rem;color:#666">${Number(item.prix).toFixed(2)} € × ${item.quantite}</div>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <button type="button" class="qty-btn" onclick="changeQty(${idx},-1)">−</button>
          <button type="button" class="qty-btn" onclick="changeQty(${idx},1)">+</button>
        </div>
      </div>`;
    })
    .join('');
  totalEl.textContent = total.toFixed(2) + ' €';
}

function openCart() {
  document.getElementById('cart-overlay')?.classList.add('open');
  document.getElementById('cart-sidebar')?.classList.add('open');
  renderCart();
}

function closeCart() {
  document.getElementById('cart-overlay')?.classList.remove('open');
  document.getElementById('cart-sidebar')?.classList.remove('open');
}

async function passerCommande() {
  const cart = loadCart();
  if (!cart.length) {
    alert('Panier vide.');
    return;
  }
  const fd = new FormData();
  fd.append('action', 'passer_commande');
  fd.append('panier', JSON.stringify(cart));
  try {
    const res = await fetch('back/commande_handler.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.success) {
      if (data.redirect) {
        alert(data.message || 'Connectez-vous.');
        window.location.href = data.redirect;
        return;
      }
      alert(data.message || 'Erreur.');
      return;
    }
    saveCart([]);
    renderCart();
    updateCartCount();
    closeCart();
    alert(data.message || 'Commande enregistrée.');
  } catch {
    alert('Erreur réseau.');
  }
}

async function logout() {
  try {
    const res = await fetch('back/logout.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) window.location.href = 'index.php';
    else window.location.href = 'index.php';
  } catch {
    window.location.href = 'index.php';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('cart-overlay')?.addEventListener('click', closeCart);
  updateCartCount();
});
