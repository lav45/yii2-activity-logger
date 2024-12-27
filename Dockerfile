FROM alpine:3.15

RUN apk upgrade --update-cache --available

# php
RUN apk add --no-cache \
    php7 \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-opcache \
    php7-dom \
    php7-xml php7-xmlwriter \
    php7-tokenizer \
    php7-sqlite3 sqlite \
    php7-pdo php7-pdo_sqlite \
    php7-xdebug

# composer
RUN apk add --no-cache git php7-phar php7-openssl php7-zip php7-iconv php7-curl
RUN wget https://getcomposer.org/installer -O - | php -- --with-openssl --install-dir=/usr/local/bin --filename=composer
