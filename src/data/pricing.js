// Tarifs indicatifs affichés dans le formulaire de contact (contact.html).
// Clé = valeur exacte du <select id="service">. Une seule source à modifier pour changer un prix
// côté formulaire ; penser à répercuter le même montant sur la carte de la page concernée.
const NBSP = '\u00A0';

export const SERVICE_PRICING = {
  'Design graphique': [
    { label: 'Identité visuelle', price: `À partir de 350${NBSP}€ HT` },
    { label: 'Mise en page', price: `À partir de 60${NBSP}€ HT / page` },
    { label: 'Supports imprimés', price: `À partir de 80${NBSP}€ HT` },
    { label: 'Charte graphique', price: `À partir de 600${NBSP}€ HT` },
  ],
};
