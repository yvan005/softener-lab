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

    item.addEventListener('mouseenter', open);
    item.addEventListener('mouseleave', close);
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

document.addEventListener('DOMContentLoaded', () => {
  mountLayout();
  initNavDropdowns();
});
