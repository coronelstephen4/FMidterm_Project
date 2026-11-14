FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data . /var/www/html/

RUN find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

CMD ["sh", "-c", "port=${PORT:-80}; sed -i \"s/80/${port}/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
