FROM php:8.2-apache

ENV APACHE_MODS_DISABLE="mpm_event mpm_worker"

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install mysqli curl \
    && rm -rf /var/lib/apt/lists/* \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite

WORKDIR /var/www/html
COPY . .

EXPOSE 80

CMD ["sh", "-c", "port=${PORT:-80}; sed -ri \"s/Listen [0-9]+/Listen ${port}/\" /etc/apache2/ports.conf; sed -ri \"s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${port}>/\" /etc/apache2/sites-availabl[...]"]
