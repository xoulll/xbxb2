FROM php:8.2-apache

ENV APACHE_MODS_DISABLE="mpm_event mpm_worker"

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install mysqli curl \
    && rm -rf /var/lib/apt/lists/* \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

WORKDIR /var/www/html
COPY . .

EXPOSE 80

CMD ["apache2-foreground"]
