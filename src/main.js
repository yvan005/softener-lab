import './style.css';
import { renderHeader } from './components/header.js';
import { renderFooter } from './components/footer.js';
import { mountHeroBackground } from './components/hero-bg.js';
import { mountBackToTop } from './components/back-to-top.js';
import { initI18n, refreshTranslations, getCurrentLang } from './i18n/i18n.js';
import { translations } from './i18n/translations.js';

function mountLayout() {
  const headerMount = document.getElementById('site-header');
  const footerMount = document.getElementById('site-footer');
  if (headerMount) headerMount.innerHTML = renderHeader();
  if (footerMount) footerMount.innerHTML = renderFooter();
}

function initNavDropdowns() {
  const supportsHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  document.querySelectorAll('.nav-item').forEach((item) => {
    const btn = item.querySelector('button');
    if (!btn) return;

    const open = () => {
      document.querySelectorAll('.nav-item.open').forEach((o) => {
        if (o !== item) {
          o.classList.remove('open');
          o.querySelector('button')?.setAttribute('aria-expanded', 'false');
        }
      });
      item.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
    };
    const close = () => {
      item.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    };

    // Le survol n'ouvre le menu que sur les appareils avec une vraie souris.
    // Sur tactile, le premier tap simule un mouseenter, ce qui obligerait
    // à taper deux fois avant que le clic ne soit pris en compte.
    if (supportsHover) {
      item.addEventListener('mouseenter', open);
      item.addEventListener('mouseleave', close);
    }
    btn.addEventListener('click', () => {
      item.classList.contains('open') ? close() : open();
    });
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-item')) {
      document.querySelectorAll('.nav-item.open').forEach((o) => {
        o.classList.remove('open');
        o.querySelector('button')?.setAttribute('aria-expanded', 'false');
      });
    }
  });
}

function initMobileMenu() {
  const burger = document.getElementById('burger-toggle');
  const navLinks = document.querySelector('.nav-links');
  if (!burger || !navLinks) return;

  burger.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('mobile-open');
    burger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    if (!isOpen) {
      document.querySelectorAll('.nav-item.open').forEach((o) => o.classList.remove('open'));
    }
  });

  // Ferme le panneau mobile si on clique en dehors, ou si on redimensionne vers desktop
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-links') && !e.target.closest('#burger-toggle')) {
      navLinks.classList.remove('mobile-open');
      burger.setAttribute('aria-expanded', 'false');
    }
  });
  window.addEventListener('resize', () => {
    if (window.innerWidth > 1000) {
      navLinks.classList.remove('mobile-open');
      burger.setAttribute('aria-expanded', 'false');
    }
  });
}

async function syncAccountMenu() {
  const avatar = document.getElementById('account-avatar');
  const nameEl = document.getElementById('account-name');
  const menu = document.getElementById('account-nav-menu');

  let data = null;
  try {
    const res = await fetch('/session-status.php', { credentials: 'same-origin' });
    data = await res.json();
  } catch (e) {
    // Si l'appel échoue, on garde l'icône et le menu par défaut (visiteur non connecté).
    return;
  }

  if (avatar && nameEl && menu && data && data.loggedIn) {
    const firstName = (data.name || '').split(' ')[0] || 'Mon compte';
    const initial = firstName.charAt(0).toUpperCase();
    const dict = translations[getCurrentLang()] || translations.fr;

    // textContent (et non innerHTML) : le nom vient de l'utilisateur, il ne doit jamais être interprété comme du HTML.
    const initialEl = document.createElement('span');
    initialEl.className = 'account-initial';
    initialEl.textContent = initial;
    avatar.replaceChildren(initialEl);
    avatar.classList.add('account-avatar--active');
    nameEl.textContent = firstName;
    menu.innerHTML = `
      <a href="/dashboard.php">${dict.nav.monEspace}</a>
      <a href="/orders.php">${dict.nav.mesCommandes || 'Mes commandes'}</a>
      <a href="/notifications.php">${dict.nav.notifications || 'Notifications'}</a>
      <a href="/profile.php">${dict.nav.monProfil || 'Mon profil'}</a>
      ${data.isAdmin ? `<a href="/admin.php">${dict.nav.administration || 'Administration'}</a>` : ''}
      <a href="/logout.php">${dict.nav.seDeconnecter}</a>
    `;
  }

  prefillContactUser(data);
}

function prefillContactUser(data) {
  const form = document.getElementById('contact-form');
  if (!form || !data || !data.loggedIn) return;

  const nameInput = document.getElementById('name');
  const emailInput = document.getElementById('email');
  if (nameInput && !nameInput.value) nameInput.value = data.name || '';
  if (emailInput && !emailInput.value) emailInput.value = data.email || '';
}

function prefillContactService() {
  const select = document.getElementById('service');
  if (!select) return;

  const params = new URLSearchParams(window.location.search);
  const wanted = params.get('service');
  if (!wanted) return;

  const matches = Array.from(select.options).some((o) => o.value === wanted);
  if (matches) select.value = wanted;
}

function mountContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;

  const feedback = document.getElementById('contact-feedback');
  const button = form.querySelector('button[type="submit"]');
  const defaultLabel = button ? button.textContent : '';

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (feedback) {
      feedback.hidden = true;
      feedback.className = 'form-feedback';
      feedback.textContent = '';
    }
    if (button) {
      button.disabled = true;
      button.textContent = 'Envoi en cours…';
    }

    try {
      const response = await fetch('/contact.php', {
        method: 'POST',
        body: new FormData(form),
      });
      const data = await response.json();

      if (data.success) {
        form.reset();
        if (feedback) {
          feedback.hidden = false;
          feedback.className = 'form-feedback is-success';
          feedback.textContent = data.message || "Merci, votre message a bien été envoyé. On revient vers vous sous 48 heures.";
        }
      } else {
        const errors = Array.isArray(data.errors) && data.errors.length
          ? data.errors.join(' ')
          : "Une erreur est survenue. Merci de réessayer.";
        if (feedback) {
          feedback.hidden = false;
          feedback.className = 'form-feedback is-error';
          feedback.textContent = errors;
        }
      }
    } catch (err) {
      if (feedback) {
        feedback.hidden = false;
        feedback.className = 'form-feedback is-error';
        feedback.textContent = "Impossible d'envoyer le message pour le moment. Réessaie dans un instant.";
      }
    } finally {
      if (button) {
        button.disabled = false;
        button.textContent = defaultLabel;
      }
    }
  });
}

const EYE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
const EYE_OFF_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.9 10.9 0 0 1 12 5c7 0 10.5 7 10.5 7a13.4 13.4 0 0 1-3.1 4"/><path d="M6.6 6.6C3.4 8.5 1.5 12 1.5 12s3.5 7 10.5 7c1.3 0 2.5-.2 3.6-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';

function initPasswordToggles() {
  document.querySelectorAll('input[type="password"]').forEach((input) => {
    if (input.closest('.password-field')) return; // déjà traité

    const wrapper = document.createElement('div');
    wrapper.className = 'password-field';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'password-toggle';
    btn.setAttribute('aria-label', 'Afficher le mot de passe');
    btn.setAttribute('aria-pressed', 'false');
    btn.tabIndex = 0;
    btn.innerHTML = EYE_ICON;
    wrapper.appendChild(btn);

    btn.addEventListener('click', () => {
      const willShow = input.type === 'password';
      input.type = willShow ? 'text' : 'password';
      btn.setAttribute('aria-pressed', willShow ? 'true' : 'false');
      btn.setAttribute('aria-label', willShow ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
      btn.innerHTML = willShow ? EYE_OFF_ICON : EYE_ICON;
    });
  });
}

/* --- Cloche de notifications (header) --- */

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

function renderNotifItems(items) {
  const list = document.getElementById('notif-list');
  if (!list) return;

  if (!items || items.length === 0) {
    list.innerHTML = '<p class="notif-empty">Aucune notification.</p>';
    return;
  }

  list.innerHTML = items.map((n) => `
    <button type="button" class="notif-item${n.isRead ? '' : ' is-unread'}" data-id="${n.id}" data-link="${n.link ? escapeHtml(n.link) : ''}">
      <span class="notif-item__title">${escapeHtml(n.title)}</span>
      <span class="notif-item__msg">${escapeHtml(n.message)}</span>
      <span class="notif-item__time">${escapeHtml(n.timeAgo)}</span>
    </button>
  `).join('');
}

async function notifAction(action, extra) {
  const body = new URLSearchParams({ action, ...extra });
  try {
    const res = await fetch('/notifications-status.php', { method: 'POST', credentials: 'same-origin', body });
    return await res.json();
  } catch (e) {
    return null;
  }
}

async function refreshNotifications() {
  const navItem = document.getElementById('notif-nav-item');
  const badge = document.getElementById('notif-badge');
  if (!navItem || !badge) return;

  let data = null;
  try {
    const res = await fetch('/notifications-status.php', { credentials: 'same-origin' });
    data = await res.json();
  } catch (e) {
    return;
  }

  if (!data || !data.loggedIn) {
    navItem.hidden = true;
    return;
  }

  navItem.hidden = false;
  if (data.count > 0) {
    badge.hidden = false;
    badge.textContent = data.count > 9 ? '9+' : String(data.count);
  } else {
    badge.hidden = true;
  }
  renderNotifItems(data.items);
}

function initNotifications() {
  const navItem = document.getElementById('notif-nav-item');
  const list = document.getElementById('notif-list');
  const markAllBtn = document.getElementById('notif-mark-all');
  if (!navItem || !list) return;

  list.addEventListener('click', async (e) => {
    const item = e.target.closest('.notif-item');
    if (!item) return;
    const id = item.dataset.id;
    const link = item.dataset.link;
    if (item.classList.contains('is-unread')) {
      await notifAction('mark_read', { id });
    }
    if (link) window.location.href = link;
    else refreshNotifications();
  });

  markAllBtn?.addEventListener('click', async () => {
    await notifAction('mark_all', {});
    refreshNotifications();
  });

  refreshNotifications();
  // Rafraîchit le compteur périodiquement pour rester à jour sans recharger la page.
  setInterval(refreshNotifications, 60000);
}

document.addEventListener('DOMContentLoaded', () => {
  mountLayout();
  initI18n();
  refreshTranslations(); // applique la langue au header/footer qui viennent d'être injectés
  initNavDropdowns();
  initMobileMenu();
  syncAccountMenu();
  mountBackToTop();
  prefillContactService();
  mountContactForm();
  initPasswordToggles();
  initNotifications();

  const heroCanvas = document.getElementById('hero-bg');
  if (heroCanvas) mountHeroBackground(heroCanvas);
});
