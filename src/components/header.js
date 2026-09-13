export function renderHeader() {
  return `
  <div class="wrap">
    <nav>
      <a href="/index.html" class="logo">
        <svg class="logo-mark" viewBox="0 0 28 28" fill="none">
          <rect x="1" y="1" width="26" height="26" rx="5" stroke="#3DDC84" stroke-width="1.4"/>
          <path d="M8 14L12.5 18.5L20 9.5" stroke="#3DDC84" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Softener Lab
      </a>
      <div class="nav-links">
        <div class="nav-item" data-nav="services">
          <button type="button" aria-expanded="false">
            Services
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="mega">
            <div class="mega-inner">
              <a class="mega-cell" href="/formations.html">
                <span class="mega-tag">FORMATION</span>
                <h5>Formations en ligne</h5>
                <p>Photoshop, InDesign, montage vidéo, cybersécurité.</p>
              </a>
              <a class="mega-cell" href="/design.html">
                <span class="mega-tag">DESIGN</span>
                <h5>Design & infographie</h5>
                <p>Identité visuelle, supports imprimés et digitaux.</p>
              </a>
              <a class="mega-cell" href="/developpement.html">
                <span class="mega-tag">DÉVELOPPEMENT</span>
                <h5>Applications & logiciels</h5>
                <p>Web, mobile et logiciels métier sur mesure.</p>
              </a>
              <a class="mega-cell" href="/cybersecurite.html">
                <span class="mega-tag">SÉCURITÉ</span>
                <h5>Cybersécurité</h5>
                <p>Audits, tests d'intrusion, accompagnement.</p>
              </a>
              <a class="mega-cell" href="/flyers.html">
                <span class="mega-tag">SUPPORTS</span>
                <h5>Flyers & templates</h5>
                <p>Modèles prêts à l'emploi ou créations sur mesure.</p>
              </a>
            </div>
          </div>
        </div>
        <div class="nav-item" data-nav="studio">
          <button type="button" aria-expanded="false">
            Studio
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/index.html#about">À propos</a>
            <a href="/contact.html">Contact</a>
          </div>
        </div>
        <a href="/contact.html">Tarifs</a>
        <div class="nav-item" data-nav="account">
          <button type="button" aria-expanded="false">
            Compte
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/login.php">Se connecter</a>
            <a href="/register.php">Créer un compte</a>
          </div>
        </div>
      </div>
      <button type="button" class="burger" id="burger-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
        <svg width="22" height="16" viewBox="0 0 22 16" fill="none">
          <line x1="0" y1="1" x2="22" y2="1" stroke="currentColor" stroke-width="2"/>
          <line x1="0" y1="8" x2="22" y2="8" stroke="currentColor" stroke-width="2"/>
          <line x1="0" y1="15" x2="22" y2="15" stroke="currentColor" stroke-width="2"/>
        </svg>
      </button>
      <a href="/contact.html" class="nav-cta">Discuter d'un projet</a>
    </nav>
  </div>
  `;
}
