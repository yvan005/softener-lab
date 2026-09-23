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
        <div class="nav-item" data-nav="design">
          <button type="button" aria-expanded="false">
            <span data-i18n="nav.design">Design</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/design-graphique.html" data-i18n="navDesign.graphique">Design graphique</a>
            <a href="/design-web-ux-ui.html" data-i18n="navDesign.webUxUi">Design web / UX-UI</a>
            <a href="/design-reseaux-sociaux.html" data-i18n="navDesign.reseauxSociaux">Design réseaux sociaux</a>
            <a href="/motion-design.html" data-i18n="navDesign.motion">Motion design</a>
            <a href="/illustration.html" data-i18n="navDesign.illustration">Illustration</a>
            <a href="/design-packaging.html" data-i18n="navDesign.packaging">Design de packaging</a>
            <a href="/design-espace-interieur.html" data-i18n="navDesign.espaceInterieur">Design d'espace / intérieur</a>
          </div>
        </div>

        <div class="nav-item" data-nav="developpement">
          <button type="button" aria-expanded="false">
            <span data-i18n="nav.developpement">Développement</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/applications-web.html" data-i18n="navDev.web">Applications web</a>
            <a href="/applications-mobiles.html" data-i18n="navDev.mobiles">Applications mobiles</a>
            <a href="/logiciels-metier.html" data-i18n="navDev.metier">Logiciels métier</a>
          </div>
        </div>

        <div class="nav-item" data-nav="cybersecurite">
          <button type="button" aria-expanded="false">
            <span data-i18n="nav.cybersecurite">Cybersécurité</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/audit-securite.html" data-i18n="navCyber.audit">Audit de sécurité</a>
            <a href="/test-intrusion.html" data-i18n="navCyber.testIntrusion">Test d'intrusion</a>
            <a href="/conseil-strategie.html" data-i18n="navCyber.conseilStrategie">Conseil et stratégie</a>
            <a href="/formation-sensibilisation.html" data-i18n="navCyber.formationSensibilisation">Formation et sensibilisation</a>
            <a href="/securite-reseau.html" data-i18n="navCyber.securiteReseau">Sécurité réseau</a>
            <a href="/reponse-incident.html" data-i18n="navCyber.reponseIncident">Réponse à incident</a>
            <a href="/securite-cloud.html" data-i18n="navCyber.securiteCloud">Sécurité cloud</a>
            <a href="/securite-applications.html" data-i18n="navCyber.securiteApplications">Sécurité des applications</a>
          </div>
        </div>

        <div class="nav-item" data-nav="flyers">
          <button type="button" aria-expanded="false">
            <span data-i18n="nav.flyers">Flyers</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/modele-evenementiel.html" data-i18n="navFlyers.evenementiel">Modèle Événementiel</a>
            <a href="/modele-promotion.html" data-i18n="navFlyers.promotion">Modèle Promotion</a>
            <a href="/modele-annonces.html" data-i18n="navFlyers.annonces">Modèle Annonces</a>
            <a href="/modele-recrutement.html" data-i18n="navFlyers.recrutement">Modèle Recrutement</a>
            <a href="/modele-associatif.html" data-i18n="navFlyers.associatif">Modèle Associatif</a>
            <a href="/modele-restaurant-menu.html" data-i18n="navFlyers.restaurantMenu">Modèle Restaurant/Menu</a>
            <a href="/modele-informatif-educatif.html" data-i18n="navFlyers.informatifEducatif">Modèle Informatif/Éducatif</a>
            <a href="/modele-politique.html" data-i18n="navFlyers.politique">Modèle Politique</a>
            <a href="/modele-invitation.html" data-i18n="navFlyers.invitation">Modèle Invitation</a>
            <a href="/modele-coupon-reduction.html" data-i18n="navFlyers.couponReduction">Modèle Coupon/Réduction</a>
          </div>
        </div>

        <div class="nav-item" data-nav="formations">
          <button type="button" aria-expanded="false">
            <span data-i18n="nav.formations">Formations</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/formation-bureautique.html" data-i18n="navFormations.bureautique">Bureautique</a>
            <a href="/formation-graphique-design.html" data-i18n="navFormations.graphiqueDesign">Graphique & Design</a>
            <a href="/formation-seo-international.html" data-i18n="navFormations.seoInternational">SEO International</a>
            <a href="/formation-programmation-python.html" data-i18n="navFormations.programmationPython">Programmation Python</a>
            <a href="/formation-cybersecurite.html" data-i18n="navFormations.cybersecurite">Cybersécurité</a>
          </div>
        </div>

        <div class="nav-item" data-nav="about">
          <button type="button" aria-expanded="false">
            <span data-i18n="nav.apropos">À propos</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </button>
          <div class="simple-menu">
            <a href="/apercu-entreprise.html" data-i18n="navAbout.apercu">Aperçu de l'entreprise</a>
            <a href="/leadership.html" data-i18n="navAbout.leadership">Leadership</a>
            <a href="/carriere.html" data-i18n="navAbout.carriere">Carrière</a>
          </div>
        </div>

        <div class="nav-item" data-nav="lang">
          <button type="button" aria-expanded="false" id="lang-nav-button" aria-label="Changer de langue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
              <path d="M3 12h18M12 3c2.5 2.6 3.8 5.7 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.7-3.8-9s1.3-6.4 3.8-9z" stroke="currentColor" stroke-width="1.4"/>
            </svg>
            <span id="lang-current">FR</span>
          </button>
          <div class="simple-menu" id="lang-nav-menu">
            <button type="button" class="lang-option" data-lang="fr">Français</button>
            <button type="button" class="lang-option" data-lang="en">English</button>
            <button type="button" class="lang-option" data-lang="es">Español</button>
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
            <span class="account-name" id="account-name" data-i18n="nav.compte">Compte</span>
          </button>
          <div class="simple-menu" id="account-nav-menu">
            <a href="/login.php" data-i18n="nav.seConnecter">Se connecter</a>
            <a href="/register.php" data-i18n="nav.creerCompte">Créer un compte</a>
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
