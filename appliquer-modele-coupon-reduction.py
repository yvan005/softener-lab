from pathlib import Path
import re

page = Path('modele-coupon-reduction.html')
if not page.exists():
    raise SystemExit('Erreur : modele-coupon-reduction.html introuvable. Lance ce script depuis la racine du projet.')

html = page.read_text(encoding='utf-8')
match = re.search(r'<!--\s*GALLERY\b(.*?)-->', html, flags=re.S)
if not match:
    raise SystemExit('Erreur : marqueur GALLERY introuvable dans modele-coupon-reduction.html.')

title = re.search(r'title="([^"]*)"', match.group(1))
title_value = title.group(1) if title else 'Exemples de modèles'
new_marker = f'<!--GALLERY title="{title_value}" layout="flyers" parallax="true" -->'
html = html[:match.start()] + new_marker + html[match.end():]

style = """\
<style id="coupon-reduction-gallery-clean">
.portfolio-showcase .section-head p,
.portfolio-showcase .p-tile span,
.portfolio-showcase .portfolio-note {
    display: none !important;
}
</style>
"""
if 'coupon-reduction-gallery-clean' not in html:
    if '</head>' not in html:
        raise SystemExit('Erreur : balise </head> introuvable.')
    html = html.replace('</head>', style + '</head>', 1)

page.write_text(html, encoding='utf-8')
print('Page Modèle Coupon/Réduction configurée : parallaxe activé, descriptions masquées.')
