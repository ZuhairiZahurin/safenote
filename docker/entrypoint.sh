#!/bin/sh
set -e

# Apache refuses to start when two MPMs are loaded, and an apt upgrade during
# the build can leave Debian's default enabled next to the prefork one mod_php
# needs. Enabling modules at build time did not hold, so the symlinks are
# settled here, where they certainly apply to the container that runs.
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
echo "MPM modules enabled: $(ls /etc/apache2/mods-enabled/ | grep mpm | tr '\n' ' ')"
echo "LoadModule mpm lines found anywhere in the config:"
grep -rn '^[[:space:]]*LoadModule[[:space:]]\+mpm' /etc/apache2/ 2>/dev/null || echo "  (none outside mods-enabled)"

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
# A failure here must not take the site down with it: the schema is already
# migrated, so the app can serve while the seeding problem is looked at.
if [ "${SEED_DEMO_DATA}" = "true" ]; then
    php artisan db:seed --force || echo "Seeding failed. The site is starting anyway; demo data is incomplete." >&2
fi

exec apache2-foreground
