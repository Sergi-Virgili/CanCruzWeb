# Assets stage - builds frontend assets
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# Vendor stage - installs Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

# PHP base shared by production and development images
FROM php:8.4-fpm-alpine AS php-base
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/html

# Runtime stage - production image
FROM php-base AS runtime
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/testing && php artisan package:discover --ansi && chown -R www-data:www-data storage bootstrap/cache
USER www-data
CMD ["php-fpm"]

# Development tools are installed before project files so this layer stays cacheable.
FROM php-base AS development-tools
USER root
RUN apk add --no-cache \
    git \
    curl \
    linux-headers \
    $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Development target
FROM development-tools AS development
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-scripts
# Copy node_modules from assets stage for volume initialization
COPY --from=assets /app/node_modules /app/node_modules
# Copy application files after the development tools so code edits do not rebuild Xdebug.
COPY . .
# Backup vendor and node_modules for volume initialization
RUN cp -r /var/www/html/vendor /var/www/html/vendor-image \
    && cp -r /app/node_modules /app/node_modules-image
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
USER www-data
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
