# Softener Lab — site vitrine

Site vitrine multi-pages (accueil + 5 pages de service + contact), construit avec [Vite](https://vitejs.dev).
Le header et le footer sont des composants partagés (`src/components/`) injectés en JavaScript sur chaque page : tu les modifies une seule fois, et le changement s'applique partout.

## Structure du projet

```
softener-lab/
├── index.html              → page d'accueil
├── formations.html         → page Formations
├── design.html              → page Design & infographie
├── developpement.html       → page Développement d'applications
├── cybersecurite.html       → page Cybersécurité
├── flyers.html               → page Flyers & templates
├── contact.html              → page Contact (formulaire)
├── src/
│   ├── style.css             → toute la feuille de style du site
│   ├── main.js                → injecte header/footer + gère le menu
│   └── components/
│       ├── header.js          → contenu du menu (modifie ici pour changer les liens partout)
│       └── footer.js          → contenu du pied de page
├── vite.config.js
└── package.json
```

## Installation (une seule fois)

Il te faut [Node.js](https://nodejs.org) installé (version 18 ou plus récente).

```bash
npm install
```

## Travailler en local

```bash
npm run dev
```

Ça ouvre le site sur `http://localhost:5173`. Toute modification d'un fichier (HTML, CSS, JS) se recharge automatiquement dans le navigateur.

## Construire la version finale (avant mise en ligne)

```bash
npm run build
```

Ça génère un dossier `dist/` avec les fichiers optimisés, prêts à être déployés sur n'importe quel hébergeur.

Pour vérifier le résultat avant de le mettre en ligne :

```bash
npm run preview
```

## Déploiement automatique (GitHub Actions)

Le site se déploie automatiquement sur InfinityFree à chaque `git push` sur la branche `main` — plus besoin de manipuler le gestionnaire de fichiers manuellement.

### Configuration à faire une seule fois

1. Sur GitHub, va dans ton dépôt > **Settings** > **Secrets and variables** > **Actions**.
2. Ajoute ces 4 secrets (**New repository secret**) :

| Nom du secret | Valeur |
|---|---|
| `FTP_USERNAME` | `if0_42901352` (ton nom d'utilisateur InfinityFree) |
| `FTP_PASSWORD` | ton mot de passe vPanel InfinityFree (sert aussi pour la base de données) |
| `SMTP_USER` | ton adresse Gmail utilisée pour l'envoi d'emails |
| `SMTP_PASS` | ton mot de passe d'application Gmail (16 caractères) |

3. Une fois les 4 secrets ajoutés, n'importe quel `git push` sur `main` déclenche automatiquement :
   - la construction du site (`npm run build`)
   - la génération de `includes/db.php` et `includes/config.php` avec les vrais identifiants (jamais stockés dans le code)
   - l'envoi de tout ça vers `htdocs/` sur InfinityFree via FTP

Tu peux suivre chaque déploiement dans l'onglet **Actions** de ton dépôt GitHub.

### En cas de souci

Le FTP d'InfinityFree (hébergement gratuit) est parfois lent ou instable. Si un déploiement échoue, relance-le simplement depuis l'onglet Actions (bouton "Re-run jobs"), ou reviens temporairement à l'upload manuel via le gestionnaire de fichiers en cas de besoin urgent.

## Comment modifier le site

- **Texte et contenu d'une page** : ouvre le fichier `.html` correspondant (ex. `formations.html`) et modifie directement le texte entre les balises.
- **Menu de navigation (en haut)** : modifie `src/components/header.js`.
- **Pied de page** : modifie `src/components/footer.js`.
- **Couleurs, polices, espacements** : tout est dans `src/style.css`. Les couleurs principales sont définies en haut du fichier dans `:root { ... }` — change une valeur là pour l'appliquer partout.
- **Ajouter une nouvelle page** : crée un nouveau fichier `.html` à la racine (copie la structure d'une page existante), puis ajoute-le dans `vite.config.js` sous `rollupOptions.input`.

## Déploiement

Le site est 100% statique une fois construit (`npm run build` → dossier `dist/`), donc il peut être déployé sur n'importe quel hébergeur de sites statiques. Trois options simples et gratuites pour démarrer :

### Option 1 — Netlify (recommandé pour débuter)
1. Crée un compte sur [netlify.com](https://netlify.com).
2. Glisse-dépose le dossier `dist/` (après `npm run build`) sur leur interface "Deploy manually".
3. Ton site est en ligne avec une URL `*.netlify.app`. Tu peux ensuite y associer ton propre nom de domaine.

### Option 2 — Vercel
1. Crée un compte sur [vercel.com](https://vercel.com).
2. Connecte ton dépôt GitHub (voir ci-dessous), Vercel détecte Vite automatiquement.
3. Chaque `git push` redéploie le site automatiquement.

### Option 3 — GitHub Pages
Plus manuel, demande de publier le contenu de `dist/` sur une branche `gh-pages`. Recommandé seulement si tu es déjà à l'aise avec Git.

### Mettre le projet sur GitHub (pour Netlify/Vercel en continu)
```bash
git init
git add .
git commit -m "Premier commit — Softener Lab"
```
Puis crée un dépôt vide sur GitHub et suis les instructions affichées pour le relier (`git remote add origin ...`).

## À faire avant la mise en production

- [ ] Remplacer le contenu placeholder des pages de service (portfolio, tarifs réels) par du contenu définitif.
- [x] Formulaire de `contact.html` connecté : il envoie un email via PHPMailer (`backend-php/contact.php`, réutilise la config SMTP déjà en place pour les comptes utilisateurs). Le message arrive dans la boîte définie par `SMTP_FROM`, avec le visiteur en Reply-To.
- [ ] Remplacer les liens `#` du footer (LinkedIn, Instagram) par tes vrais profils.
- [ ] Ajouter un favicon et les balises meta pour le référencement (titre, description) propres à chaque page.
- [x] Espace membre : session régénérée à la connexion (anti-fixation), verrouillage du compte après 5 tentatives échouées (15 min), lien de confirmation d'email expirant au bout de 24h, cookies de session `httponly`/`SameSite`. ⚠️ Si la base est déjà en prod, exécute la section "Migration" en bas de `backend-php/sql/schema.sql` pour ajouter les nouvelles colonnes.
- [ ] Espace membre — reste à faire : renvoi de l'email de confirmation, page "Mon profil".
- [x] Espace membre : "mot de passe oublié" (`forgot-password.php` → email avec lien valable 1h → `reset-password.php`). Ne révèle jamais si un email a un compte ou non. Colonnes `reset_token` / `reset_token_expires` ajoutées à `users` (migration dans `schema.sql`).

## Espace membre

Pages PHP (dans `backend-php/`) réservées aux utilisateurs connectés :

| Page | Rôle |
|---|---|
| `dashboard.php` | Statistiques, mes formations (accès actif / demande en cours), catalogue avec « Demander l'accès », annulation d'une demande en attente |
| `orders.php` | Mes commandes de services (design, développement, cybersécurité, flyers) : liste filtrable + nouvelle commande |
| `order.php` | Détail d'une commande : avancement (Reçue → En cours → Livrée), brief, annulation tant qu'elle est « Reçue » |
| `profile.php` | Modifier son nom, changer son mot de passe, supprimer son compte |
| `admin.php` | **Admin** : toutes les commandes (filtres par statut, recherche par membre, titre, service ou `CMD-0001`) |
| `admin-order.php` | **Admin** : détail d'une commande, changement de statut, message au membre, email automatique |
| `admin-trainings.php` | **Admin** : activer ou refuser les demandes d'accès aux formations (email automatique) |
| `admin-contact-messages.php` | **Admin** : messages bruts des visiteurs non connectés (formulaire de contact), marquables comme lus |

**Administration** : les comptes dont l'email figure dans `ADMIN_EMAILS` (`includes/config.template.php`, par défaut l'adresse d'envoi `SMTP_FROM`) voient un onglet « Administration ». Pour ajouter un admin : `define('ADMIN_EMAILS', [SMTP_FROM, 'toi@exemple.com']);` puis push. Le compte doit exister et avoir confirmé son email.

**Emails automatiques** : l'équipe reçoit un email à chaque nouvelle commande ou demande de formation ; le membre reçoit un email à chaque changement de statut de sa commande (si la case « Prévenir le membre » est cochée) et quand sa demande de formation est activée ou refusée. Un échec d'envoi n'empêche jamais l'enregistrement.

**Formulaire de contact (`contact.html`)** : pour un visiteur non connecté, la demande reste un simple email à l'équipe, en plus d'être archivée sur `/admin-contact-messages.php` et notifiée aux comptes admin dans la cloche. Pour un membre connecté, la même demande devient une commande de suivi (`orders`), visible dans « Mes commandes » et dans `/admin.php`, avec la même notification cloche + email aux admins que les commandes créées depuis `/orders.php`.

Les commandes utilisent la table `orders` (créée automatiquement si absente, voir aussi `sql/schema.sql`) ; leur statut se change depuis `admin-order.php` (`pending`, `in_progress`, `delivered`, `cancelled`).

Briques communes : `includes/orders.php` (catalogue de services, statuts), `includes/member.php` (gabarit, messages flash, vérification du mot de passe avec verrouillage) et `includes/csrf.php` (jeton CSRF sur tous les formulaires POST). Aucune modification de la base n'est nécessaire.
Le style de l'espace membre est dans la dernière section de `src/style.css`.
