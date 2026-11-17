#!/usr/bin/env bash
# update-and-push.sh — applique une archive .zip sur le dépôt courant, puis commit + push.
#
# Usage (à lancer à la racine du dépôt) :
#   ./update-and-push.sh ~/Downloads/softener-lab-main.zip "Message de commit"
#   ./update-and-push.sh archive.zip "Message" --delete   # supprime aussi les fichiers absents de l'archive
#
# Ne touche jamais à : .git, node_modules, softener-lab-updated.zip, ce script,
# backend-php/includes/db.php et config.php (générés au déploiement par la CI).

set -euo pipefail

ZIP="${1:-}"
MSG="${2:-}"
DELETE="${3:-}"

if [[ -z "$ZIP" || -z "$MSG" ]]; then
  echo "Usage : $0 <archive.zip> \"message de commit\" [--delete]" >&2
  exit 1
fi
[[ -f "$ZIP" ]] || { echo "Archive introuvable : $ZIP" >&2; exit 1; }
git rev-parse --is-inside-work-tree >/dev/null 2>&1 || { echo "Ce dossier n'est pas un dépôt git." >&2; exit 1; }

REPO="$(git rev-parse --show-toplevel)"
BRANCH="$(git branch --show-current)"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

# 1. Décompression
if command -v unzip >/dev/null 2>&1; then
  unzip -q "$ZIP" -d "$TMP"
else
  python3 -m zipfile -e "$ZIP" "$TMP"
fi

# 2. Si l'archive contient un seul dossier racine, on part de celui-ci
SRC="$TMP"
shopt -s nullglob dotglob
entries=("$TMP"/*)
if [[ ${#entries[@]} -eq 1 && -d "${entries[0]}" ]]; then
  SRC="${entries[0]}"
fi
shopt -u nullglob dotglob

EXCLUDES=(
  --exclude=.git
  --exclude=node_modules
  --exclude=softener-lab-updated.zip
  --exclude=update-and-push.sh
  --exclude=backend-php/includes/db.php
  --exclude=backend-php/includes/config.php
)

# 3. Suppression optionnelle des fichiers retirés de l'archive
if [[ "$DELETE" == "--delete" ]]; then
  (cd "$REPO" && git ls-files) | while IFS= read -r f; do
    case "$f" in
      softener-lab-updated.zip|update-and-push.sh|backend-php/includes/db.php|backend-php/includes/config.php) continue ;;
    esac
    [[ -e "$SRC/$f" ]] || rm -f "$REPO/$f"
  done
fi

# 4. Copie de l'archive par-dessus le dépôt
(cd "$SRC" && tar cf - "${EXCLUDES[@]}" .) | (cd "$REPO" && tar xf -)

# 5. Commit + push
cd "$REPO"
git add -A
git reset -q -- backend-php/includes/db.php backend-php/includes/config.php 2>/dev/null || true
if git diff --cached --quiet; then
  echo "Aucun changement à pousser."
  exit 0
fi

echo "Fichiers modifiés :"
git status --short
git commit -m "$MSG"
git push origin "$BRANCH"
echo "Poussé sur origin/$BRANCH."
