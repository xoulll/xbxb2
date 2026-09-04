FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install mysqli curl \
    && rm -rf /var/lib/apt/lists/* \
    && a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite \
    && sed -i '/<IfModule mpm_event_module>/,/<\/IfModule>/d' /etc/apache2/mods-enabled/*.conf 2>/dev/null || true \
    && sed -i '/<IfModule mpm_worker_module>/,/<\/IfModule>/d' /etc/apache2/mods-enabled/*.conf 2>/dev/null || true

WORKDIR /var/www/html
COPY . .

EXPOSE 80

CMD ["apache2-foreground"]
