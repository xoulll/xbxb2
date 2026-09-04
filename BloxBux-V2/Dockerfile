FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends libcurl4-openssl-dev && \
    docker-php-ext-install mysqli curl && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]