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
      <a href="/profile.php">${dict.nav.monProfil || 'Mon profil'}</a>
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
          feedback.textContent = "Merci, votre message a bien été envoyé. On revient vers vous sous 48 heures.";
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

  const heroCanvas = document.getElementById('hero-bg');
  if (heroCanvas) mountHeroBackground(heroCanvas);
});
