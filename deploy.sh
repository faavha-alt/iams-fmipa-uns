#!/usr/bin/env bash
# Deploy IAMS FMIPA UNS ke server produksi.
# Alur: pastikan working tree bersih -> push ke origin -> SSH ke server -> pull, composer, migrate, clear cache.
#
# Pakai:
#   ./deploy.sh        # akan minta konfirmasi sebelum deploy di server
#   ./deploy.sh -y      # skip konfirmasi
#
# Syarat: alias SSH "iams-fmipa" sudah ada di ~/.ssh/config (lihat CLAUDE.md bagian Deploy).

set -euo pipefail

REMOTE_ALIAS="iams-fmipa"
REMOTE_PATH="~/htdocs/aset.mipa.uns.ac.id"
BRANCH="main"

cd "$(dirname "$0")"

current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [[ "$current_branch" != "$BRANCH" ]]; then
  echo "Bukan di branch $BRANCH (sekarang: $current_branch). Deploy dibatalkan." >&2
  exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Ada perubahan yang belum di-commit:" >&2
  git status --short
  exit 1
fi

echo "==> git push origin $BRANCH"
git push origin "$BRANCH"

if [[ "${1:-}" != "-y" ]]; then
  read -r -p "Lanjut deploy ke server ($REMOTE_ALIAS)? (migrate --force akan dijalankan) [y/N] " confirm
  if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
    echo "Deploy dibatalkan."
    exit 1
  fi
fi

echo "==> Deploy di server ($REMOTE_ALIAS)..."
ssh "$REMOTE_ALIAS" bash -s <<EOF
set -e
cd $REMOTE_PATH
echo "--> git pull"
git pull
echo "--> composer install"
COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader
echo "--> migrate"
php artisan migrate --force
echo "--> clear cache"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
EOF

echo "==> Deploy selesai."
