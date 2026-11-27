from pathlib import Path
import re

page = Path('modele-politique.html')
if not page.exists():
    raise SystemExit('Erreur : modele-politique.html introuvable. Lance ce script depuis la racine du projet.')

html = page.read_text(encoding='utf-8')
match = re.search(r'<!--\s*GALLERY\b(.*?)-->', html, flags=re.S)
if not match:
    raise SystemExit('Erreur : marqueur GALLERY introuvable dans modele-politique.html.')

attrs = match.group(1)
title = re.search(r'title="([^"]*)"', attrs)
title_value = title.group(1) if title else 'Exemples de modèles'
new_marker = f'<!--GALLERY title="{title_value}" layout="flyers" parallax="true"-->'
html = html[:match.start()] + new_marker + html[match.end():]

style = """\
<style id="politique-gallery-clean">
.portfolio-showcase .section-head p,
.portfolio-showcase .p-tile span,
.portfolio-showcase .portfolio-note {
    display: none !important;
}
</style>
"""
if 'politique-gallery-clean' not in html:
    if '</head>' not in html:
        raise SystemExit('Erreur : balise </head> introuvable.')
    html = html.replace('</head>', style + '</head>', 1)

page.write_text(html, encoding='utf-8')
print('Page Modèle Politique configurée : parallaxe activé, descriptions masquées.')
