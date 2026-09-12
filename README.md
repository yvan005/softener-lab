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
- [ ] Connecter le formulaire de `contact.html` à un service comme [Formspree](https://formspree.io) ou les "Netlify Forms" pour recevoir réellement les messages (actuellement le formulaire ne fait qu'afficher les champs, il n'envoie rien).
- [ ] Remplacer les liens `#` du footer (LinkedIn, Instagram) par tes vrais profils.
- [ ] Ajouter un favicon et les balises meta pour le référencement (titre, description) propres à chaque page.
