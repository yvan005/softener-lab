# Softener Lab — amélioration du Hero

Cette version conserve la structure et les fonctionnalités du projet d'origine.
Les changements de cette première étape concernent uniquement `src/style.css` :

- apparition progressive des éléments du Hero ;
- mouvement flottant discret du panneau des cinq métiers ;
- animation de circulation sur les connexions du schéma SVG ;
- pulsation subtile du nœud principal et de l'indicateur actif ;
- interactions plus fluides sur le bouton principal et le panneau ;
- ajustements d'espacement et de typographie sur mobile ;
- prise en compte de `prefers-reduced-motion` pour les personnes qui limitent les animations.

Aucun contenu, lien, formulaire, fichier PHP ou mécanisme de traduction n'a été supprimé.

## Étape 2 — corrections

- Connexions du schéma : trait vert translucide (au lieu du gris très sombre) pour un effet réellement lumineux.
- Flèche « scroll-cue » : suppression d'une règle de survol sans effet (l'animation de rebond reste prioritaire).
- Performance :
  - la pulsation du nœud central n'anime plus `filter` (halo statique, animation par opacité et échelle) ;
  - les animations CSS du hero et la boucle du canvas de fond sont suspendues quand le hero n'est plus visible (`src/components/hero-bg.js`, classe `is-offscreen`) ;
  - sur écrans ≤ 600 px, l'animation de circulation des lignes SVG est désactivée.
