FROM phpdockerio/php74-cli:latest

ARG UID
ARG GID

RUN usermod --non-unique --uid ${UID} www-data
RUN groupmod --non-unique --gid ${GID} www-data

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y apt-utils

# php extension
RUN apt-get install -y php-memcached php-sqlite3 sqlite3

# composer
RUN apt-get install -y git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# clean
RUN rm -rf /var/lib/apt/lists/*
RUN apt-get autoremove -y
RUN apt-get clean
