FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
 && docker-php-ext-install mysqli curl \
 && rm -rf /var/lib/apt/lists/* \
 && rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
 && a2enmod mpm_prefork rewrite

WORKDIR /var/www/html
COPY . .

EXPOSE 80

CMD ["apache2-foreground"]
