#!/usr/bin/env bash
# Deploy the site to perso.ensta.fr/~saood.
#
#   ./deploy.sh          upload the built site (_site/) and clean up stale files
#   ./deploy.sh init     ONE TIME: create the CMS data folder on the server,
#                        import cms-seed/ (existing posts & news) and print the setup token
#
# Build first (bundle exec jekyll build, or docker compose) so _site/ is fresh.
# Content you edit in /admin/ lives in ~/cms-data on the server, outside
# public_html, so deploying never overwrites it.
#
# Remote commands are kept on one line so they work whatever your login shell is.
set -euo pipefail

JUMP="${JUMP:-saood@relais.ensta.fr}"
HOST="${HOST:-saood@salle.ensta.fr}"
REMOTE_HOME="${REMOTE_HOME:-/home/uei/saood}"
WEB="$REMOTE_HOME/public_html"
DATA="$REMOTE_HOME/cms-data"
# The Unix user PHP runs as. tools/server-check.php prints it ("Runs as user").
PHP_USER="${PHP_USER:-www-data}"

remote() { ssh -o "ProxyJump=$JUMP" "$HOST" "$@"; }

cd "$(dirname "$0")"

if [[ "${1:-}" == "init" ]]; then
  script=$(mktemp)
  cat > "$script" <<EOF
set -e
DATA="$DATA"; WEB="$WEB"; PHP_USER="$PHP_USER"
mkdir -p "\$DATA"/posts "\$DATA"/news "\$DATA"/messages "\$DATA"/history "\$DATA"/trash "\$DATA"/sessions "\$DATA"/state "\$WEB/files/uploads"

# Import existing posts/news without overwriting anything already there.
tmp=\$(mktemp -d)
tar -xzf - -C "\$tmp"
for kind in posts news; do
  for f in "\$tmp/\$kind"/*.md; do
    [ -e "\$f" ] || continue
    [ -e "\$DATA/\$kind/\$(basename "\$f")" ] || cp "\$f" "\$DATA/\$kind/"
  done
done
rm -rf "\$tmp"

if [ "\$PHP_USER" = "\$(id -un)" ]; then
  chmod 700 "\$DATA"
  echo "PHP runs as you: no extra permissions needed."
elif command -v setfacl >/dev/null 2>&1 && setfacl -m "u:\$PHP_USER:rwx" "\$DATA" 2>/dev/null; then
  setfacl -R -m "u:\$PHP_USER:rwX" -m "d:u:\$PHP_USER:rwX" "\$DATA" "\$WEB/files"
  chmod -R o-rwx "\$DATA"
  echo "Gave \$PHP_USER access with ACLs; other users cannot read cms-data."
else
  chmod -R a+rwX "\$DATA" "\$WEB/files"
  echo "WARNING: no ACL support, so cms-data and files/ were made world-writable for PHP."
fi

if ! grep -q password_hash "\$DATA/settings.json" 2>/dev/null; then
  head -c 24 /dev/urandom | od -An -tx1 | tr -d ' \n' > "\$DATA/SETUP_TOKEN"
  chmod a+r "\$DATA/SETUP_TOKEN"
  echo
  echo "Setup token (paste it at https://perso.ensta.fr/~saood/admin/):"
  cat "\$DATA/SETUP_TOKEN"; echo
fi
rm -f "\$HOME/.cms-init.sh"
EOF
  echo "==> Uploading setup script"
  remote "cat > .cms-init.sh" < "$script"
  rm -f "$script"
  echo "==> Creating $DATA and importing cms-seed/ (existing files are kept)"
  tar -C cms-seed -czf - . | remote "bash .cms-init.sh"
  exit 0
fi

if [[ ! -f _site/blog/index.php ]]; then
  echo "_site/ is missing or outdated: build the site first." >&2
  exit 1
fi

echo "==> Uploading _site/ to $HOST:$WEB"
# Also removes files from older versions that would shadow the new PHP pages or are unsafe.
tar -C _site -czf - . | remote "tar -xzf - -C $WEB && rm -f $WEB/simple-admin.php $WEB/server-check.php $WEB/blog/index.html $WEB/news/index.html $WEB/admin/index.html $WEB/admin/config.yml && mkdir -p $WEB/files && echo Done."
