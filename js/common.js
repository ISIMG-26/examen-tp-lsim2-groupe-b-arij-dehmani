// Fonctions communes utilisées dans plusieurs pages

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function logout() {
    const fd = new FormData();
    fd.append('action', 'deconnexion');
    fetch('back/auth_handler.php', { method: 'POST', credentials: 'same-origin', body: fd })
        .then((r) => r.json())
        .then((d) => { if (d.success) location.href = 'index.php'; });
}