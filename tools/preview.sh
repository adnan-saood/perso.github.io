#!/usr/bin/env bash
# Local preview WITH PHP, so the blog, news and /admin/ work like on the server.
# (`jekyll serve` cannot run PHP: blog/news show a folder listing and the
# homepage news box stays empty.)
#
#   1. build:    bundle exec jekyll build        (or with --config _config.yml,_config_dev.yml)
#   2. preview:  bash tools/preview.sh           (on Windows: wsl bash tools/preview.sh)
#   3. open the URL it prints
#
# With Docker you don't need this by hand: `docker compose up` runs it in a PHP 8.4
# container next to Jekyll and serves everything on http://localhost:8080.
#
# Content edited in the preview goes to .preview-data/ (not committed); delete
# that folder to start again from cms-seed/.
set -euo pipefail
cd "$(dirname "$0")/.."
PORT="${PORT:-8081}"
LISTEN="${LISTEN:-localhost}"

if [[ -n "${WAIT_FOR_BUILD:-}" ]]; then
  echo "Waiting for Jekyll to finish its first build..."
  until [[ -f _site/blog/index.php && -f _site/index.html ]]; do sleep 2; done
fi
if [[ ! -f _site/blog/index.php ]]; then
  echo "_site/ is missing or was built from an older version: run 'bundle exec jekyll build' first." >&2
  exit 1
fi
command -v php >/dev/null || { echo "PHP is not installed (Ubuntu/WSL: sudo apt install php-cli)." >&2; exit 1; }

# Use the same base URL the site was built with ("/~saood/" or "/" for the dev config).
BASE=$(grep -o 'data-baseurl="[^"]*"' _site/index.html | head -1 | cut -d'"' -f2)
BASE="${BASE:-/}"

DATA="$PWD/.preview-data"
if [[ ! -d "$DATA" ]]; then
  mkdir -p "$DATA"
  cp -r cms-seed/posts cms-seed/news cms-seed/projects cms-seed/publications cms-seed/*.json "$DATA/"
  echo "preview-setup-token-local" > "$DATA/SETUP_TOKEN"
fi

echo "Site:   http://localhost:$PORT$BASE"
echo "Admin:  http://localhost:$PORT${BASE}admin/"
[[ -f "$DATA/SETUP_TOKEN" ]] && echo "        first-time setup token: $(cat "$DATA/SETUP_TOKEN")"
echo "Ctrl+C to stop."
mkdir -p "$DATA/files"
CMS_DATA_DIR="$DATA" CMS_FILES_DIR="$DATA/files" CMS_BASE_URL="$BASE" exec php -S "$LISTEN:$PORT" tools/preview-router.php
