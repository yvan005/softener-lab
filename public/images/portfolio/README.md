# Galeries « Exemples de réalisations »

Chaque page de service a son dossier : `public/images/portfolio/<nom-de-la-page>/`
(ex. `audit-securite/`, `modele-promotion/`, `formation-bureautique/`).

**Pour alimenter une galerie, déposez simplement vos images dans le dossier de la page** :

- jusqu'à **6 images** affichées (les 6 premières, par ordre alphabétique du nom de fichier) ;
- formats `.jpg`, `.jpeg`, `.png`, `.webp`, `.avif` ; idéalement **900 × 675 px** (4:3), JPEG ≤ 150 Ko ;
- l'**étiquette** sous l'image vient du nom du fichier : `02-site-vitrine.jpg` devient « Site vitrine » ;
  préfixez par `01-`, `02-`… pour choisir l'ordre ;
- pour des **accents ou une apostrophe** dans l'étiquette, ajoutez un fichier `labels.json` dans le dossier
  (voir `design-web-ux-ui/labels.json`) : `{ "01-maquette-sombre.jpg": "Maquette sombre" }` ;
  évitez les accents dans les noms de fichiers eux-mêmes (déploiement FTP) ;
- dossier vide = **aucune galerie** sur la page (rien d'affiché, pas de trou).

Pour voir les emplacements vides en local : `GALLERY_PLACEHOLDERS=1 npm run dev`.
Les 6 pages Design (web / UX-UI, réseaux sociaux, motion, illustration, packaging, espace) affichent toujours
leurs 6 emplacements « Visuel à venir » tant qu'aucune image n'est déposée : c'est l'attribut `empty="true"`
du marqueur `<!--GALLERY …-->` dans leur HTML (à ajouter sur une autre page pour obtenir le même comportement).

La page `design-graphique.html` a sa propre galerie, écrite directement dans le HTML.
