# Production image for SafeNote. Apache serves public/ directly; no Node build
# step is needed because Bootstrap is loaded from a CDN and the one stylesheet
# of our own is already a static file.
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Laravel's pretty URLs and .htaccess rules need mod_rewrite, and the document
# root must be public/ so nothing above it is ever served.
# The Debian image ships AllowOverride None, which makes Apache ignore Laravel's
# .htaccess and turn every route except the home page into a 404.
# Installing packages can leave Debian's default MPM enabled alongside the
# prefork one mod_php needs, and Apache refuses to start with two of them
# loaded, so exactly one is settled on here.
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true; \
    a2enmod mpm_prefork rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencies first, so a code change does not reinstall them every build.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8080
ENTRYPOINT ["entrypoint"]
