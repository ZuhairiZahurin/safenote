#!/bin/sh
set -e

# Railway hands the container a port to listen on; fall back for a local run.
PORT="${PORT:-8080}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Fails loudly rather than booting a site that cannot decrypt its own records.
if [ -z "${APP_KEY}" ]; then
    echo "APP_KEY is not set. Counselling records cannot be decrypted without it." >&2
    exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# Set SEED_DEMO_DATA=true for the very first deploy only, then remove it.
if [ "${SEED_DEMO_DATA}" = "true" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground
