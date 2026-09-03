FROM php:8.2-apache

RUN docker-php-ext-install mysqli curl \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . .

EXPOSE 80

CMD ["sh", "-c", "port=${PORT:-80}; sed -ri \"s/Listen [0-9]+/Listen ${port}/\" /etc/apache2/ports.conf; sed -ri \"s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${port}>/\" /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]