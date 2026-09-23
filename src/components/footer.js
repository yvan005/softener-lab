export function renderFooter() {
  return `
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-col">
        <h4>SOFTENER LAB</h4>
        <p style="color:#8D95A3;" data-i18n="footer.tagline">Studio de formation, design, développement et cybersécurité.</p>
      </div>
      <div class="foot-col">
        <h4 data-i18n="footer.modules">MODULES</h4>
        <a href="/formations.html" data-i18n="footer.formations">Formations</a>
        <a href="/design.html" data-i18n="footer.design">Design</a>
        <a href="/developpement.html" data-i18n="footer.developpement">Développement</a>
        <a href="/cybersecurite.html" data-i18n="footer.cybersecurite">Cybersécurité</a>
        <a href="/flyers.html" data-i18n="footer.flyers">Flyers</a>
      </div>
      <div class="foot-col">
        <h4 data-i18n="footer.studio">STUDIO</h4>
        <a href="/apercu-entreprise.html" data-i18n="footer.apropos">À propos</a>
        <a href="/contact.html" data-i18n="footer.contact">Contact</a>
      </div>
      <div class="foot-col">
        <h4 data-i18n="footer.reseaux">RÉSEAUX</h4>
        <a href="#">LinkedIn</a>
        <a href="#">Instagram</a>
      </div>
    </div>
    <div class="foot-bottom">
      <span data-i18n="footer.rights">© 2026 Softener Lab</span>
      <span data-i18n="footer.baseline">Design. Develop. Protect.</span>
    </div>
  </div>
  `;
}
