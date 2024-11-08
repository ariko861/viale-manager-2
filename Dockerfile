FROM php:8.3-apache AS base

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pdo zip mbstring pdo_mysql intl pdo_pgsql mysqli
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

FROM base AS prod
# Copy Laravel app files
COPY . /var/www/html
# Set write permissions to used folders
RUN chown -R www-data:www-data /var/www/html /var/www/html/storage /var/www/html/bootstrap/cache
# Change working directory to Laravel app root

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html
ENV ENVIRONNEMENT=PROD
# Install composer and Laravel dependencies with composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --optimize-autoloader #--no-dev --optimize-autoloader \
# Expose port 80 for Apache \
EXPOSE 80


FROM base AS beta

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html /var/www/html/storage /var/www/html/bootstrap/cache
RUN cp /var/www/html/.docker/php.ini "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html
ENV ENVIRONNEMENT=BETA

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --optimize-autoloader #--no-dev --optimize-autoloader \
# Expose port 80 for Apache \
EXPOSE 80

#CMD ["php", "artisan", "filament:cache-components"]


FROM base AS dev
#EXPOSE 80
WORKDIR /app
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
ENV ENVIRONNEMENT=DEV

