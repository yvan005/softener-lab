// Bouton flottant "remonter en haut" : apparaît après un peu de défilement
// et ramène en douceur en haut de la page au clic.

const SHOW_AFTER_PX = 480;

export function mountBackToTop() {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'back-to-top';
  btn.setAttribute('aria-label', 'Remonter en haut de la page');
  btn.innerHTML = `
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
      <path d="M12 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      <path d="M5 12L12 5L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  `;

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  document.body.appendChild(btn);

  const toggle = () => {
    if (window.scrollY > SHOW_AFTER_PX) {
      btn.classList.add('is-visible');
    } else {
      btn.classList.remove('is-visible');
    }
  };

  window.addEventListener('scroll', toggle, { passive: true });
  toggle();
}
