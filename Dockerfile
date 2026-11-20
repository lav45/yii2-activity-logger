FROM php:8.2-cli-alpine

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache git unzip \
 && install-php-extensions intl zip xdebug \
 && rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
