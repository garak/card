FROM composer:2.7 AS composer
FROM php:8.3-cli-alpine

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /srv/card
