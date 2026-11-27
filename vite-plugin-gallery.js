// Galerie « Exemples de modèles » (6 images maximum) construite à la compilation.
//
// Sur chaque page concernée, un marqueur  <!--GALLERY title="…" lead="…"-->  indique où insérer la galerie.
// Les images se déposent dans  public/images/portfolio/<nom-de-la-page>/  (ex. audit-securite/) :
//   - formats .jpg .jpeg .png .webp .avif et vidéos .mp4 .webm, triés par nom de fichier (préfixer 01-, 02-… pour choisir l'ordre) ;
//   - une vidéo est lue en boucle, sans son, quand la vignette est visible. Une image du même nom (01-ma-video.jpg
//     à côté de 01-ma-video.mp4) sert d'aperçu et n'est pas comptée comme une vignette ; vidéos à compresser (≈ 500 Ko) ;
//   - l'étiquette sous chaque image vient du nom du fichier : « 02-site-vitrine.jpg » → « Site vitrine » ;
//     pour des accents ou une apostrophe, ajouter un fichier labels.json dans le dossier : { "02-site-vitrine.jpg": "Site vitrine" } ;
//   - les 6 premières images sont affichées. Aucune image → aucune galerie (la page reste propre).
// Pour voir les emplacements vides pendant le développement :  GALLERY_PLACEHOLDERS=1 npm run dev
// Dispositions spéciales :  layout="web" (grand écran + tablette + téléphone), layout="phones" (téléphones décalés),
// layout="stack" (écran principal + modules). Avec  parallax="true", les vignettes se décalent légèrement au défilement
// (src/main.js : désactivé si le visiteur réduit les animations ou économise les données).  note="…"  remplace la note du bas.
// Un marqueur peut porter  ratio="1:1"  : vignettes carrées (idéal pour des posts de réseaux sociaux ; images en 900 × 900).
// Il peut aussi porter  empty="true"  : la page montre alors ses 6 emplacements vides tant qu'aucune image n'est déposée.
//
// Même module : le marqueur  <!--PRICE service="Applications web"-->  insère un bandeau « À partir de X € HT +
// Nous contacter » (« Demander un devis » pour un membre connecté, voir main.js) à partir de src/data/pricing.js (une seule source de prix pour les formulaires et les pages).
import fs from 'node:fs';
import path from 'node:path';
import { SERVICE_PRICING } from './src/data/pricing.js';

const IMG = /\.(jpe?g|png|webp|avif)$/i;
const VID = /\.(mp4|webm)$/i;
const EXT = /\.(jpe?g|png|webp|avif|mp4|webm)$/i;
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const attr = (src, name) => (src.match(new RegExp(`${name}="([^"]*)"`)) || [])[1];

const labelOf = (file) => {
  const base = file.replace(EXT, '').replace(/^\d+[\s._-]*/, '').replace(/[-_]+/g, ' ').trim();
  return base ? base.charAt(0).toUpperCase() + base.slice(1) : 'Modèle';
};

const priceParts = (text) => {
  const m = String(text).match(/^(À partir de)\s+(.+?)[\u00A0 ]€ HT(?: \/ (.+))?$/);
  return m ? { from: m[1], amount: m[2], unit: m[3] || '' } : null;
};

function renderPriceBand(service) {
  const entry = SERVICE_PRICING[service];
  const parts = entry && entry.price ? priceParts(entry.price) : null;
  if (!parts) return '';
  const note = `Prix en euros HT. ${entry.note ? `${entry.note} ` : ''}Un devis précis est établi selon votre projet.`;
  return `<section class="price-band">
  <div class="wrap">
    <div class="price-band__box">
      <div class="price-band__main">
        <span class="price-band__label">Tarif indicatif</span>
        <div class="price-band__price"><span class="price-band__from">${esc(parts.from)}</span> <strong>${esc(parts.amount)}&nbsp;€</strong> <span class="price-band__tax">HT</span>${parts.unit ? ` <span class="price-band__unit">/ ${esc(parts.unit)}</span>` : ''}</div>
        <p class="price-band__note">${esc(note)}</p>
      </div>
      <a class="btn-primary" href="/contact.html?service=${encodeURIComponent(service)}" data-member-label="Demander un devis">Nous contacter</a>
    </div>
  </div>
</section>
`;
}

export function portfolioGallery({ max = 6 } = {}) {
  let root = process.cwd();
  return {
    name: 'portfolio-gallery',
    configResolved(config) {
      root = config.root;
    },
    transformIndexHtml: {
      order: 'pre',
      handler(html, ctx) {
        html = html.replace(/<!--PRICE\s+service="([^"]*)"\s*-->/, (_, service) => renderPriceBand(service.replace(/&amp;/g, '&')));
        const marker = html.match(/<!--GALLERY([\s\S]*?)-->/);
        if (!marker) return html;

        const slug = path.basename(ctx.filename || ctx.path || '').replace(/\.html?$/, '');
        const securityPages = new Set([
          'audit-securite', 'test-intrusion', 'conseil-strategie',
          'formation-sensibilisation', 'securite-reseau', 'reponse-incident',
          'securite-cloud', 'securite-applications',
        ]);
        const securityClass = securityPages.has(slug) ? ' portfolio-showcase--security' : '';
        const dir = path.join(root, 'public', 'images', 'portfolio', slug);
        const all = fs.existsSync(dir) ? fs.readdirSync(dir).filter((f) => EXT.test(f)) : [];
        const stem = (f) => f.replace(EXT, '');
        const videoStems = new Set(all.filter((f) => VID.test(f)).map(stem));
        const poster = (video) => all.find((f) => IMG.test(f) && stem(f) === stem(video));
        const files = all
          .filter((f) => VID.test(f) || !videoStems.has(stem(f))) // une image du même nom qu'une vidéo = son aperçu
          .sort((a, b) => a.localeCompare(b, 'fr', { numeric: true }))
          .slice(0, max);
        const showEmpty = process.env.GALLERY_PLACEHOLDERS === '1' || attr(marker[1], 'empty') === 'true';
        if (!files.length && !showEmpty) return html.replace(marker[0], '');

        const square = attr(marker[1], 'ratio') === '1:1';
        const layout = ['web', 'phones', 'stack', 'flyers'].includes(attr(marker[1], 'layout')) ? attr(marker[1], 'layout') : '';
        const parallax = layout && attr(marker[1], 'parallax') === 'true';
        const note = attr(marker[1], 'note');
        const title = attr(marker[1], 'title') || 'Exemples de modèles';
        const lead = attr(marker[1], 'lead') || 'Un aperçu de nos modèles dans ce domaine.';

        let custom = {};
        try {
          custom = JSON.parse(fs.readFileSync(path.join(dir, 'labels.json'), 'utf-8'));
        } catch {
          /* pas de labels.json : étiquettes déduites des noms de fichiers */
        }

        const tiles = files.map((file) => {
          const label = custom[file] || labelOf(file);
          const url = `/images/portfolio/${slug}/${encodeURIComponent(file)}`;
          if (VID.test(file)) {
            const p = poster(file);
            const posterAttr = p ? ` poster="/images/portfolio/${slug}/${encodeURIComponent(p)}"` : '';
            const posterImg = p ? `<img class="p-poster" src="/images/portfolio/${slug}/${encodeURIComponent(p)}" alt="" loading="lazy">` : '';
            return `      <div class="p-tile has-img has-video"><video src="${url}"${posterAttr} muted loop playsinline preload="none" aria-label="${esc(label)}"></video>${posterImg}<span>${esc(label)}</span></div>`;
          }
          return `      <div class="p-tile has-img"><img src="${url}" alt="${esc(label)}" loading="lazy"><span>${esc(label)}</span></div>`;
        });
        if (showEmpty) {
          while (tiles.length < max) tiles.push('      <div class="p-tile is-placeholder"><span>Visuel à venir</span></div>');
        }

        const block = `<section class="portfolio-showcase${securityClass}">
  <div class="wrap">
    <div class="section-head">
      <h2>${esc(title)}</h2>
      <p>${esc(lead)}</p>
    </div>
    <div class="portfolio-bento${square ? ' portfolio-bento--square' : ''}${layout ? ` portfolio-bento--${layout}` : ''}"${parallax ? ` data-parallax="${layout}"` : ''}>
${tiles.join('\n')}
    </div>
    <div class="portfolio-note"><span class="dot"></span> ${files.length ? esc(note || "D'autres modèles seront ajoutés régulièrement à cette galerie.") : 'Portfolio en cours de constitution — les premiers visuels seront ajoutés ici.'}</div>
  </div>
</section>
`;
        return html.replace(marker[0], block);
      },
    },
  };
}
