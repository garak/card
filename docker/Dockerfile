FROM composer:2.5 AS composer
FROM php:8.2-cli-alpine

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /srv/card
