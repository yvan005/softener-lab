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
        <div class="nav-item" data-nav="formations">
          <button type="button" aria-expanded="false">
            Formations
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/photoshop-fondamentaux.html">Photoshop — Fondamentaux</a>
            <a href="/indesign-mise-en-page.html">InDesign — Mise en page</a>
            <a href="/montage-video.html">Montage vidéo</a>
            <a href="/cybersecurite-bases.html">Cybersécurité — Les bases</a>
            <a href="/cybersecurite-audit.html">Cybersécurité — Audit & pentest</a>
          </div>
        </div>

        <div class="nav-item" data-nav="design">
          <button type="button" aria-expanded="false">
            Design
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/identite-visuelle.html">Identité visuelle</a>
            <a href="/supports-imprimes.html">Supports imprimés</a>
            <a href="/creation-video.html">Création vidéo</a>
          </div>
        </div>

        <div class="nav-item" data-nav="developpement">
          <button type="button" aria-expanded="false">
            Développement
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/applications-web.html">Applications web</a>
            <a href="/applications-mobiles.html">Applications mobiles</a>
            <a href="/logiciels-metier.html">Logiciels métier</a>
          </div>
        </div>

        <div class="nav-item" data-nav="cybersecurite">
          <button type="button" aria-expanded="false">
            Cybersécurité
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/audit-securite.html">Audit de sécurité</a>
            <a href="/test-intrusion.html">Test d'intrusion</a>
            <a href="/sensibilisation-equipes.html">Sensibilisation des équipes</a>
            <a href="/accompagnement-continu.html">Accompagnement continu</a>
          </div>
        </div>

        <div class="nav-item" data-nav="flyers">
          <button type="button" aria-expanded="false">
            Flyers
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/modele-evenementiel.html">Modèle Événementiel</a>
            <a href="/modele-promotion.html">Modèle Promotion</a>
            <a href="/modele-annonce.html">Modèle Annonce</a>
          </div>
        </div>

        <div class="nav-item" data-nav="about">
          <button type="button" aria-expanded="false">
            À propos
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/apercu-entreprise.html">Aperçu de l'entreprise</a>
            <a href="/leadership.html">Leadership</a>
            <a href="/carriere.html">Carrière</a>
          </div>
        </div>

        <div class="nav-item" data-nav="account">
          <button type="button" aria-expanded="false" id="account-nav-button">
            <span class="account-avatar" id="account-avatar">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.6"/>
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
            </span>
            <span class="account-name" id="account-name">Compte</span>
          </button>
          <div class="simple-menu" id="account-nav-menu">
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
    </nav>
  </div>
  `;
}
