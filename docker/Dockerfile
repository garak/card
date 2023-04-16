FROM composer:2.5 AS composer
FROM php:8.2-cli-alpine

# Force php timezone
RUN echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/docker-php-datetime.ini

# Install Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /srv/card
