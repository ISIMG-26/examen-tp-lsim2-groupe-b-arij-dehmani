/** dashboard.php — admin produits (AJAX) + déconnexion */
function imgUrl(p) {
    const img = (p.image || '').trim();
    if (!img) return '';
    if (img.startsWith('http')) return img;
    return 'images/' + img.replace(/^\/+/, '');
}
async function dashLoad() {
    const box = document.getElementById('admin-products');
    if (!box) return;
    box.textContent = '…';
    try {
        const res = await fetch('back/produits_handler.php?action=get_all_admin', { credentials: 'same-origin' });
        const text = await res.text();
        console.log('Dashboard get_all_admin response', res.status, text);
        if (!res.ok) {
            console.error('Dashboard get_all_admin HTTP error', res.status, text);
            box.textContent = text ? `Erreur serveur (${res.status}) : ${text}` : `Erreur serveur (${res.status})`;
            return;
        }
        let d;
        try {
            d = JSON.parse(text);
        } catch (err) {
            box.textContent = 'Erreur JSON: réponse invalide';
            console.error('Dashboard get_all_admin JSON parse error', text);
            const snippet = text.length > 300 ? text.slice(0, 300) + '...' : text;
            console.error('Response content:', snippet);
            return;
        }
        if (!d.success || !d.produits?.length) {
            box.textContent = d.message ? d.message : 'Aucun produit.';
            return;
        }
        const produits = d.produits;
        box.innerHTML =
            '<table class="data-table"><thead><tr>' +
            '<th></th><th>ID</th><th>Nom</th><th>Prix</th><th>Dispo</th><th></th>' +
            '</tr></thead><tbody>' +
            produits.map((p) => {
                const src = imgUrl(p);
                const thumb = src
                    ? `<img src="${esc(src)}" alt="" width="44" height="44" style="object-fit:cover;border-radius:8px" loading="lazy">`
                    : '—';
                return `<tr><td>${thumb}</td><td>${p.id}</td><td>${esc(p.nom)}</td>` +
                    `<td>${parseFloat(p.prix).toFixed(2)} €</td>` +
                    `<td>${String(p.disponible) === '1' ? 'oui' : 'non'}</td>` +
                    `<td><button type="button" class="btn-secondary" onclick="dashToggle(${p.id},${p.disponible})">On/Off</button> ` +
                    `<button type="button" class="btn-secondary" onclick="dashDel(${p.id})">Suppr</button></td></tr>`;
            }).join('') +
            '</tbody></table>';
    } catch (err) {
        box.textContent = 'Erreur réseau ou serveur.';
        console.error('Dashboard get_all_admin error', err);
    }
}
async function dashToggle(id, cur) {
    const fd = new FormData();
    fd.append('action', 'modifier_dispo');
    fd.append('id', id);
    fd.append('disponible', String(cur) === '1' ? '0' : '1');
    await fetch('back/produits_handler.php', { method: 'POST', credentials: 'same-origin', body: fd });
    dashLoad();
}
async function dashDel(id) {
    if (!confirm('Supprimer ?')) return;
    const fd = new FormData();
    fd.append('action', 'supprimer');
    fd.append('id', id);
    const d = await (await fetch('back/produits_handler.php', { method: 'POST', credentials: 'same-origin', body: fd })).json();
    if (!d.success) return alert(d.message || 'Erreur');
    dashLoad();
}
async function dashAdd() {
    const nom = document.getElementById('p-nom').value.trim();
    const prix = parseFloat(document.getElementById('p-prix').value);
    const cat = document.getElementById('p-cat').value;
    const url = document.getElementById('p-image-url').value.trim();
    const file = document.getElementById('p-image-file').files?.[0];
    if (!nom || !prix || !cat) return alert('Nom, prix et catégorie requis.');
    const fd = new FormData();
    fd.append('action', 'ajouter');
    fd.append('nom', nom);
    fd.append('description', '');
    fd.append('prix', String(prix));
    fd.append('stock', '100');
    fd.append('categorie_id', cat);
    fd.append('image_url', url);
    if (file) fd.append('image', file);
    const d = await (await fetch('back/produits_handler.php', { method: 'POST', credentials: 'same-origin', body: fd })).json();
    if (!d.success) return alert(d.message || 'Erreur');
    ['p-nom', 'p-prix', 'p-cat', 'p-image-url'].forEach((id) => {
        const e = document.getElementById(id);
        if (e) e.value = '';
    });
    const f = document.getElementById('p-image-file');
    if (f) f.value = '';
    dashLoad();
}
document.addEventListener('DOMContentLoaded', dashLoad);
