// Galerie « Exemples de réalisations » (6 images maximum) construite à la compilation.
//
// Sur chaque page concernée, un marqueur  <!--GALLERY title="…" lead="…"-->  indique où insérer la galerie.
// Les images se déposent dans  public/images/portfolio/<nom-de-la-page>/  (ex. audit-securite/) :
//   - formats .jpg .jpeg .png .webp .avif, triés par nom de fichier (préfixer 01-, 02-… pour choisir l'ordre) ;
//   - l'étiquette sous chaque image vient du nom du fichier : « 02-site-vitrine.jpg » → « Site vitrine » ;
//   - les 6 premières images sont affichées. Aucune image → aucune galerie (la page reste propre).
// Pour voir les emplacements vides pendant le développement :  GALLERY_PLACEHOLDERS=1 npm run dev
import fs from 'node:fs';
import path from 'node:path';

const EXT = /\.(jpe?g|png|webp|avif)$/i;
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const attr = (src, name) => (src.match(new RegExp(`${name}="([^"]*)"`)) || [])[1];

const labelOf = (file) => {
  const base = file.replace(EXT, '').replace(/^\d+[\s._-]*/, '').replace(/[-_]+/g, ' ').trim();
  return base ? base.charAt(0).toUpperCase() + base.slice(1) : 'Réalisation';
};

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
        const marker = html.match(/<!--GALLERY([\s\S]*?)-->/);
        if (!marker) return html;

        const slug = path.basename(ctx.filename || ctx.path || '').replace(/\.html?$/, '');
        const dir = path.join(root, 'public', 'images', 'portfolio', slug);
        const files = fs.existsSync(dir)
          ? fs.readdirSync(dir).filter((f) => EXT.test(f)).sort((a, b) => a.localeCompare(b, 'fr', { numeric: true })).slice(0, max)
          : [];
        const showEmpty = process.env.GALLERY_PLACEHOLDERS === '1';
        if (!files.length && !showEmpty) return html.replace(marker[0], '');

        const title = attr(marker[1], 'title') || 'Exemples de réalisations';
        const lead = attr(marker[1], 'lead') || 'Un aperçu de nos réalisations dans ce domaine.';

        const tiles = files.map((file) => {
          const label = labelOf(file);
          return `      <div class="p-tile has-img"><img src="/images/portfolio/${slug}/${encodeURIComponent(file)}" alt="${esc(label)}" loading="lazy"><span>${esc(label)}</span></div>`;
        });
        if (showEmpty) {
          while (tiles.length < max) tiles.push('      <div class="p-tile is-placeholder"><span>Visuel à venir</span></div>');
        }

        const block = `<section class="portfolio-showcase">
  <div class="wrap">
    <div class="section-head">
      <h2>${esc(title)}</h2>
      <p>${esc(lead)}</p>
    </div>
    <div class="portfolio-bento">
${tiles.join('\n')}
    </div>
    <div class="portfolio-note"><span class="dot"></span> D'autres réalisations seront ajoutées régulièrement à cette galerie.</div>
  </div>
</section>
`;
        return html.replace(marker[0], block);
      },
    },
  };
}
