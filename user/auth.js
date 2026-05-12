'use strict';

let _session  = null;
let pendingCb = null;
let authMode  = 'login';

// Relative to index.php at the project root
const API = 'api/auth';

async function fetchCurrentSession() {
    // Fast path: PHP injected the session into the page
    if (window.__SF_SESSION !== undefined) {
        _session = window.__SF_SESSION.logged_in ? window.__SF_SESSION.user : null;
        syncAuthNavUI();
        if (_session && typeof loadCartFromDB        === 'function') loadCartFromDB();
        if (_session && typeof loadWishlistFromDB    === 'function') loadWishlistFromDB();

        if (!_session && window.__SF_OPEN_LOGIN) openLoginAuto(window.__SF_OPEN_LOGIN);
        return _session;
    }
    try {
        const res  = await fetch(`${API}/session.php`);
        const data = await res.json();
        _session = data.logged_in ? data.user : null;
    } catch {
        _session = null;
    }
    syncAuthNavUI();
    if (_session && typeof loadCartFromDB   === 'function') loadCartFromDB();
    if (_session && typeof loadWishlistFromDB === 'function') loadWishlistFromDB();
    if (!_session && window.__SF_OPEN_LOGIN) openLoginAuto(window.__SF_OPEN_LOGIN);
    return _session;
}

function openLoginAuto(role) {
    showLoginModal();
    if (role === 'farmer') {
        const radio = document.querySelector('input[name="login-role"][value="farmer"]');
        if (radio) radio.checked = true;
    }
}

function getSession() { return _session; }

function getRoleSelection(groupName) {
    return document.querySelector(`input[name="${groupName}"]:checked`)?.value || 'consumer';
}

function showAuthMessage(message, type = 'error') {
    const el = document.getElementById('auth-message');
    if (!el) return;
    el.textContent = message;
    el.classList.remove('hidden', 'ok', 'error');
    el.classList.add(type === 'ok' ? 'ok' : 'error');
}

function clearAuthMessage() {
    const el = document.getElementById('auth-message');
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('ok', 'error');
    el.textContent = '';
}

function switchAuthMode(mode) {
    authMode = mode;
    clearAuthMessage();
    const loginForm    = document.getElementById('auth-login-form');
    const registerForm = document.getElementById('auth-register-form');
    const loginBtn     = document.getElementById('mode-login-btn');
    const registerBtn  = document.getElementById('mode-register-btn');
    const title        = document.getElementById('auth-modal-title');
    const subtitle     = document.getElementById('auth-modal-subtitle');
    const isLogin      = mode === 'login';
    loginForm?.classList.toggle('hidden', !isLogin);
    registerForm?.classList.toggle('hidden', isLogin);
    loginBtn?.classList.toggle('active', isLogin);
    registerBtn?.classList.toggle('active', !isLogin);
    if (title)    title.textContent    = isLogin ? 'Connexion' : 'Créer un compte';
    if (subtitle) subtitle.textContent = isLogin
        ? 'Connectez-vous pour continuer'
        : 'Inscrivez-vous comme Farmer ou Consumer';
}

function toggleRegisterFields() {
    const role        = getRoleSelection('register-role');
    const farmerFields = document.getElementById('farmer-extra-fields');
    farmerFields?.classList.toggle('hidden', role !== 'farmer');
}

function showLoginModal(cb) {
    if (_session?.role === 'consumer') { if (typeof cb === 'function') cb(); return; }
    if (_session?.role === 'farmer')   { window.location.href = 'user/farmer-dashboard.php'; return; }
    pendingCb = cb || null;
    const modal = document.getElementById('login-modal');
    if (!modal) return;
    clearAuthMessage();
    switchAuthMode('login');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('login-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    pendingCb = null;
    clearAuthMessage();
}

function authSuccess(user) {
    _session = user;
    syncAuthNavUI();
    if (user.role === 'farmer') { window.location.href = 'user/farmer-dashboard.php'; return; }
    const callback = pendingCb;
    closeModal();
    // Merge guest cart then reload from DB
    const syncTasks = [];
    if (typeof loadCartFromDB        === 'function') syncTasks.push(loadCartFromDB());
    if (typeof loadWishlistFromDB    === 'function') syncTasks.push(loadWishlistFromDB());

    Promise.all(syncTasks).then(() => {
        if (typeof callback === 'function') callback();
        pendingCb = null;
    });
    if (!callback && typeof showPage === 'function') showPage('home');
    if (typeof toast === 'function') toast(`Bienvenue ${user.full_name} !`);
}

async function handleLogin(event) {
    event.preventDefault();
    clearAuthMessage();
    const btn        = event.target.querySelector('[type="submit"]');
    const identifier = document.getElementById('login-identifier')?.value.trim();
    const password   = document.getElementById('login-password')?.value;
    const role       = getRoleSelection('login-role');

    if (!identifier || !password) {
        showAuthMessage('Veuillez renseigner votre identifiant et mot de passe.');
        return;
    }
    if (btn) btn.disabled = true;
    try {
        const res  = await fetch(`${API}/login.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ identifier, password, role }),
        });
        const data = await res.json();
        if (data.success) {
            showAuthMessage('Connexion réussie.', 'ok');
            setTimeout(() => authSuccess(data.user), 320);
        } else {
            showAuthMessage(data.message || 'Identifiants incorrects.');
        }
    } catch {
        showAuthMessage('Erreur de connexion. Vérifiez votre réseau.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function handleRegister(event) {
    event.preventDefault();
    clearAuthMessage();
    const btn      = event.target.querySelector('[type="submit"]');
    const fullName = document.getElementById('register-full-name')?.value.trim();
    const phone    = document.getElementById('register-phone')?.value.trim();
    const email    = document.getElementById('register-email')?.value.trim().toLowerCase() || '';
    const password = document.getElementById('register-password')?.value;
    const role     = getRoleSelection('register-role');
    const wilaya   = document.getElementById('register-wilaya')?.value.trim() || '';
    const commune  = document.getElementById('register-commune')?.value.trim() || '';

    if (!fullName || !phone || !password) {
        showAuthMessage('Nom, téléphone et mot de passe sont obligatoires.');
        return;
    }
    if (role === 'farmer' && !wilaya) {
        showAuthMessage('La wilaya est obligatoire pour les agriculteurs.');
        return;
    }
    if (btn) btn.disabled = true;
    try {
        const res  = await fetch(`${API}/register.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ full_name: fullName, phone, email, password, role, wilaya, commune }),
        });
        const data = await res.json();
        if (data.success) {
            showAuthMessage('Compte créé avec succès.', 'ok');
            setTimeout(() => authSuccess(data.user), 380);
        } else {
            showAuthMessage(data.message || "Erreur lors de l'inscription.");
        }
    } catch {
        showAuthMessage('Erreur de connexion. Vérifiez votre réseau.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

function handleAuthEsc(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('login-modal');
        if (modal && !modal.classList.contains('hidden')) closeModal();
        const profileModal = document.getElementById('profile-modal');
        if (profileModal && !profileModal.classList.contains('hidden')) closeProfileModal();
    }
}

function roleLabel(role) { return role === 'farmer' ? 'Farmer' : 'Consumer'; }

function syncAuthNavUI() {
    const navBtn = document.getElementById('auth-nav-btn');
    if (!navBtn) return;
    navBtn.textContent = _session ? 'Profil' : 'Connexion';
}

function handleAuthNavClick() {
    if (!_session) { showLoginModal(); return; }
    showProfileModal();
}

function showProfileModal() {
    if (!_session) { showLoginModal(); return; }
    const modal = document.getElementById('profile-modal');
    if (!modal) return;
    const role = roleLabel(_session.role);
    const nameEl      = document.getElementById('profile-name');
    const roleBadgeEl = document.getElementById('profile-role-badge');
    const phoneEl     = document.getElementById('profile-phone');
    const wilayaEl    = document.getElementById('profile-wilaya');
    if (nameEl)      nameEl.textContent   = _session.full_name || '-';
    if (roleBadgeEl) roleBadgeEl.textContent = role;
    if (phoneEl)     phoneEl.textContent  = _session.phone  || '-';
    if (wilayaEl)    wilayaEl.textContent = _session.wilaya || '-';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if (typeof loadUserOrders === 'function') loadUserOrders();
}

function closeProfileModal() {
    const modal = document.getElementById('profile-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function logoutAuthUser() {
    try { await fetch(`${API}/logout.php`, { method: 'POST' }); } catch {}
    _session = null;
    // Clear local cart on logout (items are persisted in DB)
    if (typeof cart !== 'undefined') {
        cart = [];
        if (typeof updateBadges === 'function') updateBadges();
    }
    closeProfileModal();
    closeModal();
    syncAuthNavUI();
    if (typeof toast === 'function') toast('Déconnecté avec succès.');
}

document.addEventListener('DOMContentLoaded', () => {
    const loginForm    = document.getElementById('auth-login-form');
    const registerForm = document.getElementById('auth-register-form');
    loginForm?.addEventListener('submit', handleLogin);
    registerForm?.addEventListener('submit', handleRegister);
    toggleRegisterFields();
    fetchCurrentSession();
});

document.addEventListener('keydown', handleAuthEsc);

window.showLoginModal    = showLoginModal;
window.closeModal        = closeModal;
window.switchAuthMode    = switchAuthMode;
window.toggleRegisterFields = toggleRegisterFields;
window.handleAuthNavClick   = handleAuthNavClick;
window.showProfileModal     = showProfileModal;
window.closeProfileModal    = closeProfileModal;
window.logoutAuthUser       = logoutAuthUser;