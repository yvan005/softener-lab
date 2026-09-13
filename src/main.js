import './style.css';
import { renderHeader } from './components/header.js';
import { renderFooter } from './components/footer.js';

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
    if (window.innerWidth > 860) {
      navLinks.classList.remove('mobile-open');
      burger.setAttribute('aria-expanded', 'false');
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  mountLayout();
  initNavDropdowns();
  initMobileMenu();
});
