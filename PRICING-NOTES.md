# Tarifs indicatifs — méthode et repères de marché

Les prix affichés dans les formulaires (`src/data/pricing.js`) sont des tarifs **« à partir de », en euros HT**.
Méthode : on part des fourchettes relevées sur le marché francophone (freelances, places de marché, organismes
de formation), on se place dans la **partie basse** de la fourchette, et on tient compte du fait que les demandes
venant du Cameroun ont des budgets plus bas (la plupart des demandes de logo du pays sur Graphiste.com sont
sous 300 €). Ce sont des **ordres de grandeur** relevés en octobre 2026, pas une étude officielle : le devis reste la référence.

| Service | Repères relevés | Tarif affiché |
|---|---|---|
| Design graphique — identité visuelle | logo freelance 300 – 2 000 € (courant 600 – 1 500 €) | dès 350 € |
| Design graphique — mise en page | 80 – 200 € / page | dès 60 € / page |
| Design graphique — supports imprimés | carte de visite 50 – 200 €, flyer simple 250 – 500 € | dès 80 € |
| Design graphique — charte graphique | 500 – 15 000 € (moyenne 1 000 – 2 500 €) | dès 600 € |
| Design web / UX-UI | budget moyen de projets ≈ 1 450 €, offres 3 pages ≈ 125 – 200 € | dès 350 € |
| Design réseaux sociaux | 100 – 300 € / visuel, dégressif en pack | dès 80 € / visuel |
| Motion design | animation courte ≈ 375 € (moyenne plateforme) à 1 500 € (indépendant) | dès 400 € |
| Illustration | budget moyen ≈ 670 €, TJM moyen ≈ 120 € | dès 120 € / illustration |
| Design de packaging | TJM junior ≈ 250 €, étiquettes simples < 300 € | dès 250 € |
| Design d'espace / intérieur | visite-conseil 150 – 500 €, aménagement 20 – 100 € / m² | dès 150 € |
| Applications web | site vitrine ≈ 1 000 € (CMS : 1 500 – 4 000 €) ; vitrine simple en Afrique de l'Ouest dès ≈ 530 € | dès 900 € |
| Applications mobiles | petits MVP 500 – 1 000 € (plateformes) ; agences 25 000 € et plus | dès 1 500 € |
| Logiciels métier | outils sur mesure 15 000 – 30 000 € (agences) ; petits outils freelance bien moins | dès 2 000 € |
| Audit de sécurité | scan + audit léger 800 – 2 500 € | dès 800 € |
| Test d'intrusion | pentest PME 4 000 – 15 000 € ; périmètre réduit nettement moins | dès 1 500 € |
| Conseil et stratégie | TJM senior ≈ 350 € (plateformes) ; 700 – 1 200 € (cabinets) | dès 350 € / jour |
| Formation et sensibilisation | 415 – 850 € / participant (inter-entreprises) ; session ≈ 430 – 1 040 € en Afrique de l'Ouest | dès 400 € / session |
| Sécurité réseau | audit de configuration ≈ 2 jours | dès 700 € |
| Réponse à incident | TJM 700 – 1 000 € (cabinets) | dès 450 € / jour |
| Sécurité cloud | audit de configuration ≈ 2 jours | dès 700 € |
| Sécurité des applications | audit applicatif léger 800 – 2 500 € | dès 1 000 € |
| Modèles (flyers, invitations, coupons…) | flyer freelance 250 – 500 € ; offres d'entrée de gamme dès ≈ 15 – 20 € ; menu de restaurant ≈ 65 € en moyenne | dès 40 € (menu : dès 60 €) |
| Formation Bureautique | 340 – 375 € / jour / personne ; e-learning 200 – 330 € | dès 150 € / personne |
| Formation Cybersécurité | sensibilisation 110 – 475 € / personne | dès 150 € / personne |
| Formation Graphique & Design | repère bureautique / infographie ≈ 340 € / jour | dès 200 € / personne |
| Formation Programmation Python | cours particuliers ≈ 31 € / h ; stages en entreprise 1 350 – 2 000 € | dès 250 € / personne |
| Formation SEO International | formations en ligne 550 – 2 100 € (typiquement 700 – 1 600 €) | dès 450 € / personne |

Chaque page de service affiche un bandeau « Tarif indicatif + Demander un devis » généré automatiquement depuis `src/data/pricing.js`
(marqueur `<!--PRICE service="…"-->` dans le HTML), et les formulaires lisent le même fichier.

Pour modifier un prix : éditer `src/data/pricing.js`. Pour « Design graphique », répercuter aussi le montant sur la carte
correspondante de `design-graphique.html`.
