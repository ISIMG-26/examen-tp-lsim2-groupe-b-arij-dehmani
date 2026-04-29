// Menu client : catégories, recherche, grille produits (API publique).
let catalogProducts = [];
let selectedCategoryId = null;
let searchDebounce = null;

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

async function fetchJSON(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error('HTTP ' + res.status);
  return res.json();
}

async function loadCategories() {
  const bar = document.getElementById('categories-bar');
  if (!bar) return;
  try {
    const data = await fetchJSON('back/produits_handler.php?action=get_categories');
    if (!data.success) throw new Error();
    const cats = data.categories || [];
    bar.innerHTML = [
      '<button type="button" class="cat-btn active" data-id="">Tous</button>',
      ...cats.map(
        (c) =>
          `<button type="button" class="cat-btn" data-id="${Number(c.id)}">${esc(c.nom)}</button>`
      ),
    ].join('');
    bar.querySelectorAll('.cat-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        bar.querySelectorAll('.cat-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        const raw = btn.getAttribute('data-id');
        selectedCategoryId = raw === '' || raw === null ? null : raw;
        loadProducts();
      });
    });
  } catch {
    bar.innerHTML = '<p>Impossible de charger les catégories.</p>';
  }
}

async function loadProducts() {
  const grid = document.getElementById('products-grid');
  if (!grid) return;
  grid.textContent = 'Chargement...';
  const params = new URLSearchParams({ action: 'get_produits' });
  if (selectedCategoryId) params.set('categorie_id', String(selectedCategoryId));
  const q = document.getElementById('search-input')?.value.trim();
  if (q) params.set('search', q);
  try {
    const data = await fetchJSON('back/produits_handler.php?' + params.toString());
    if (!data.success) throw new Error();
    const list = data.produits || [];
    catalogProducts = list;
    if (!list.length) {
      grid.innerHTML = '<p>Aucun produit pour ce filtre.</p>';
      return;
    }
    grid.innerHTML = list
      .map((p) => {
        const img = esc(p.image || '');
        const nom = esc(p.nom || '');
        const descRaw = (p.description || '').slice(0, 140);
        const desc = esc(descRaw);
        const prix = parseFloat(p.prix).toFixed(2);
        const catLabel = p.categorie_nom ? esc(p.categorie_nom) : '';
        return `<article class="product-card">
        <img src="${img}" alt="" style="width:100%;height:150px;object-fit:cover;border-radius:12px 12px 0 0;display:block" loading="lazy">
        <div class="product-body">
          <div class="product-name">${nom}</div>
          <div class="product-desc">${catLabel ? `<span style="opacity:.85">${catLabel}</span> — ` : ''}${desc}</div>
          <div class="product-footer">
            <span class="product-price">${prix} €</span>
            <button type="button" class="add-to-cart-btn" title="Ajouter au panier" data-id="${Number(p.id)}">+</button>
          </div>
        </div>
      </article>`;
      })
      .join('');
  } catch {
    grid.innerHTML = '<p>Impossible de charger les produits.</p>';
    catalogProducts = [];
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const grid = document.getElementById('products-grid');
  grid?.addEventListener('click', (e) => {
    const btn = e.target.closest('.add-to-cart-btn');
    if (!btn || !grid.contains(btn)) return;
    const id = parseInt(btn.getAttribute('data-id'), 10);
    const p = catalogProducts.find((x) => Number(x.id) === id);
    if (p) addToCart({ id: p.id, nom: p.nom, prix: parseFloat(p.prix) });
  });

  document.getElementById('search-input')?.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(loadProducts, 300);
  });

  loadCategories().then(() => loadProducts());
});
