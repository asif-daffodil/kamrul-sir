#!/usr/bin/env bash

# ==============================================================================
# Daily Automated Database and Storage Backup Script
# Creates compressed mysqldump and public storage tarball with retention pruning.
# ==============================================================================

set -eo pipefail

APP_NAME="footwear-brand"
BACKUP_DIR="/var/backups/${APP_NAME}"
SHARED_STORAGE="/var/www/${APP_NAME}/shared/storage/app/public"
ENV_FILE="/var/www/${APP_NAME}/shared/.env"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=14

mkdir -p "${BACKUP_DIR}"

# 1. Parse DB credentials directly from shared production .env file
if [ ! -f "${ENV_FILE}" ]; then
    echo "ERROR: Environment file ${ENV_FILE} not found."
    exit 1
fi

DB_HOST=$(grep -E '^DB_HOST=' "${ENV_FILE}" | cut -d '=' -f2- | tr -d '\"' | tr -d "'")
DB_PORT=$(grep -E '^DB_PORT=' "${ENV_FILE}" | cut -d '=' -f2- | tr -d '\"' | tr -d "'")
DB_DATABASE=$(grep -E '^DB_DATABASE=' "${ENV_FILE}" | cut -d '=' -f2- | tr -d '\"' | tr -d "'")
DB_USERNAME=$(grep -E '^DB_USERNAME=' "${ENV_FILE}" | cut -d '=' -f2- | tr -d '\"' | tr -d "'")
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "${ENV_FILE}" | cut -d '=' -f2- | tr -d '\"' | tr -d "'")

DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}

echo "================================================================="
echo " Starting Daily Backup: ${TIMESTAMP}"
echo " Database: ${DB_DATABASE}"
echo "================================================================="

DB_BACKUP_FILE="${BACKUP_DIR}/${APP_NAME}_db_${TIMESTAMP}.sql.gz"
MEDIA_BACKUP_FILE="${BACKUP_DIR}/${APP_NAME}_media_${TIMESTAMP}.tar.gz"

# 2. Dump MySQL database using single-transaction and quick streaming to gzip
echo "==> Exporting and compressing MySQL database..."
MYSQL_PWD="${DB_PASSWORD}" mysqldump \
    --host="${DB_HOST}" \
    --port="${DB_PORT}" \
    --user="${DB_USERNAME}" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    --set-gtid-purged=OFF \
    "${DB_DATABASE}" | gzip -9 > "${DB_BACKUP_FILE}"

echo "Database backup created: ${DB_BACKUP_FILE} ($(du -sh "${DB_BACKUP_FILE}" | cut -f1))"

# 3. Archive user-uploaded footwear photos and inspection assets
if [ -d "${SHARED_STORAGE}" ]; then
    echo "==> Compressing public media and upload storage..."
    tar -czf "${MEDIA_BACKUP_FILE}" -C "$(dirname "${SHARED_STORAGE}")" "$(basename "${SHARED_STORAGE}")"
    echo "Media backup created: ${MEDIA_BACKUP_FILE} ($(du -sh "${MEDIA_BACKUP_FILE}" | cut -f1))"
fi

# 4. Enforce retention policy: Purge backups older than RETENTION_DAYS
echo "==> Purging backups older than ${RETENTION_DAYS} days..."
find "${BACKUP_DIR}" -type f -name "${APP_NAME}_*.gz" -mtime +"${RETENTION_DAYS}" -exec rm -f {} \;

# 5. Lock backup files permissions
chmod 600 "${BACKUP_DIR}"/*

echo "================================================================="
echo " Daily Backup Successfully Completed! Total Backups Retained:"
ls -lh "${BACKUP_DIR}"
echo "================================================================="
