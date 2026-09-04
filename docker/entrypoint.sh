#!/bin/sh
set -e

if [ -n "${PORT:-}" ] && [ "$PORT" != "80" ]; then
    sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

php artisan migrate --force
php artisan storage:link --force
if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi
if [ -n "${SUPERADMIN_PASSWORD:-}" ]; then
    echo "Updating superadmin credentials..."
    php scripts/create_superadmin.php
else
    echo "SUPERADMIN_PASSWORD is not configured; keeping the existing superadmin password."
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground