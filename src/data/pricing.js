// Tarifs indicatifs (€ HT) affichés dans les formulaires : contact.html et « Passer une commande »
// de l'espace membre. Clé = nom du service tel qu'il apparaît dans la liste déroulante.
//   - { price }  : un seul tarif « à partir de » pour le service
//   - { offers } : plusieurs prestations chiffrées → le formulaire affiche aussi un choix « Prestation »
//   - note (facultatif) : précision ajoutée sous le tarif (ex. « Impression non comprise. »)
// Une seule source à modifier pour changer un prix côté formulaires (pensez à répercuter le même
// montant sur la carte de design-graphique.html si vous changez une de ses prestations).
// Prix fondés sur des fourchettes du marché (freelances francophones), côté bas — voir PRICING-NOTES.md.
const N = '\u00A0'; // espace insécable avant « € »
const PRINT = 'Impression non comprise.'; // précisé uniquement pour les services imprimables
const from = (amount, unit = '') => `À partir de ${amount}${N}€ HT${unit ? ` / ${unit}` : ''}`;

export const SERVICE_PRICING = {
  // ── Formations (par personne, parcours complet en ligne) ──────────────────────────────────
  'Formation Bureautique': { price: from(150, 'personne') },
  'Formation Cybersécurité': { price: from(150, 'personne') },
  'Formation Graphique & Design': { price: from(200, 'personne') },
  'Formation Programmation Python': { price: from(250, 'personne') },
  'Formation SEO International': { price: from(450, 'personne') },

  // ── Design & infographie ──────────────────────────────────────────────────────────────────
  'Design graphique': {
    note: PRINT,
    offers: [
      { label: 'Identité visuelle', price: from(350) },
      { label: 'Mise en page', price: from(60, 'page') },
      { label: 'Supports imprimés', price: from(80) },
      { label: 'Charte graphique', price: from(600) },
    ],
  },
  'Design web / UX-UI': { price: from(350) },
  'Design réseaux sociaux': { price: from(80, 'visuel') },
  'Motion design': { price: from(400) },
  'Illustration': { price: from(120, 'illustration') },
  'Design de packaging': { price: from(250), note: PRINT },
  "Design d'espace / intérieur": { price: from(150) },

  // ── Développement d'applications ──────────────────────────────────────────────────────────
  'Applications web': { price: from(900) },
  'Applications mobiles': { price: from(1500) },
  'Logiciels métier': { price: from(2000) },

  // ── Cybersécurité ─────────────────────────────────────────────────────────────────────────
  'Audit de sécurité': { price: from(800) },
  "Test d'intrusion": { price: from(1500) },
  'Conseil et stratégie': { price: from(350, 'jour') },
  'Formation et sensibilisation': { price: from(400, 'session') },
  'Sécurité réseau': { price: from(700) },
  'Réponse à incident': { price: from(450, 'jour') },
  'Sécurité cloud': { price: from(700) },
  'Sécurité des applications': { price: from(1000) },

  // ── Flyers & templates (personnalisation d'un modèle, par document) ───────────────────────
  'Modèle Événementiel': { price: from(40), note: PRINT },
  'Modèle Promotion': { price: from(40), note: PRINT },
  'Modèle Annonces': { price: from(40), note: PRINT },
  'Modèle Recrutement': { price: from(40), note: PRINT },
  'Modèle Associatif': { price: from(40), note: PRINT },
  'Modèle Restaurant / Menu': { price: from(60), note: PRINT },
  'Modèle Informatif / Éducatif': { price: from(40), note: PRINT },
  'Modèle Politique': { price: from(40), note: PRINT },
  'Modèle Invitation': { price: from(40), note: PRINT },
  'Modèle Coupon / Réduction': { price: from(40), note: PRINT },
};

/** Valeur d'un <select> de service → nom du service (« design|Motion design » → « Motion design »). */
export const serviceName = (value) => {
  const v = String(value || '');
  return v.includes('|') ? v.slice(v.indexOf('|') + 1) : v;
};
