#!/usr/bin/env bash

# ==============================================================================
# Zero-Downtime Deployment Script for Laravel (Ubuntu VPS)
# Target Application: Mobile-first Women Footwear eCommerce Platform
# ==============================================================================

set -eo pipefail

# Configuration
APP_NAME="footwear-brand"
BASE_DIR="/var/www/${APP_NAME}"
SHARED_DIR="${BASE_DIR}/shared"
RELEASES_DIR="${BASE_DIR}/releases"
CURRENT_LINK="${BASE_DIR}/current"
BRANCH="main"
REPOSITORY="git@github.com:yourusername/footwear-brand.git" # Update with your repo SSH URL
KEEP_RELEASES=5
TIMESTAMP=$(date +"%Y%m%d%H%M%S")
RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"

echo "================================================================="
echo " Starting Zero-Downtime Deployment: ${TIMESTAMP}"
echo "================================================================="

# 1. Ensure required directories exist
mkdir -p "${SHARED_DIR}"
mkdir -p "${SHARED_DIR}/storage"
mkdir -p "${SHARED_DIR}/storage/app"
mkdir -p "${SHARED_DIR}/storage/app/public"
mkdir -p "${SHARED_DIR}/storage/framework"
mkdir -p "${SHARED_DIR}/storage/framework/cache"
mkdir -p "${SHARED_DIR}/storage/framework/sessions"
mkdir -p "${SHARED_DIR}/storage/framework/views"
mkdir -p "${SHARED_DIR}/storage/logs"
mkdir -p "${RELEASES_DIR}"

if [ ! -f "${SHARED_DIR}/.env" ]; then
    echo "ERROR: Shared production environment file ${SHARED_DIR}/.env not found!"
    exit 1
fi

# 2. Clone fresh release from Git
echo "==> Cloning release from ${BRANCH} branch..."
git clone --depth 1 --branch "${BRANCH}" "${REPOSITORY}" "${RELEASE_DIR}"

cd "${RELEASE_DIR}"

# 3. Link shared files and persistent storage
echo "==> Linking shared .env and persistent storage..."
rm -rf "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/storage" "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/.env" "${RELEASE_DIR}/.env"

# 4. Install PHP composer production dependencies
echo "==> Installing Composer production dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --quiet

# 5. Build production frontend assets (Tailwind + Vite)
echo "==> Building frontend assets with Vite..."
if [ -f "package.json" ]; then
    npm ci --no-audit --prefer-offline --quiet
    npm run build --quiet
fi

# 6. Execute database migrations inside safe transaction
echo "==> Running database migrations..."
php artisan migrate --force --isolated

# 7. Warm up production caches
echo "==> Caching Laravel configuration, routes, events, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Ensure storage symlink exists
echo "==> Creating storage symlink..."
php artisan storage:link --quiet || true

# 9. Set strict filesystem permissions
echo "==> Adjusting permissions for www-data..."
sudo chown -R www-data:www-data "${RELEASE_DIR}"
sudo chown -R www-data:www-data "${SHARED_DIR}/storage"
sudo chmod -R 775 "${SHARED_DIR}/storage"
sudo chmod -R 775 "${RELEASE_DIR}/bootstrap/cache"

# 10. Atomic symlink switch (Zero-downtime cutover)
echo "==> Activating new release atomically..."
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"

# 11. Restart queue workers and reload PHP-FPM
echo "==> Restarting Supervisor queue workers..."
php "${CURRENT_LINK}/artisan" queue:restart

echo "==> Reloading PHP 8.3 FPM..."
sudo systemctl reload php8.3-fpm

# 12. Clean up old releases (keep latest $KEEP_RELEASES)
echo "==> Purging old releases (keeping last ${KEEP_RELEASES})..."
cd "${RELEASES_DIR}"
ls -1dt 20* | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf

echo "================================================================="
echo " Deployment Successfully Completed! Release: ${TIMESTAMP}"
echo "================================================================="
