# Galeries « Exemples de réalisations »

Chaque page de service a son dossier : `public/images/portfolio/<nom-de-la-page>/`
(ex. `audit-securite/`, `modele-promotion/`, `formation-bureautique/`).

**Pour alimenter une galerie, déposez simplement vos images dans le dossier de la page** :

- jusqu'à **6 images** affichées (les 6 premières, par ordre alphabétique du nom de fichier) ;
- formats `.jpg`, `.jpeg`, `.png`, `.webp`, `.avif` ; idéalement **900 × 675 px** (4:3), JPEG ≤ 150 Ko ;
- l'**étiquette** sous l'image vient du nom du fichier : `02-site-vitrine.jpg` devient « Site vitrine » ;
  préfixez par `01-`, `02-`… pour choisir l'ordre ;
- dossier vide = **aucune galerie** sur la page (rien d'affiché, pas de trou).

Pour voir les emplacements vides en local : `GALLERY_PLACEHOLDERS=1 npm run dev`.

La page `design-graphique.html` a sa propre galerie, écrite directement dans le HTML.
