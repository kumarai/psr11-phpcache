FROM krai/php:latest

RUN pecl install mongodb \
    &&  docker-php-ext-enable mongodb

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev \
    && pecl install memcached-3.1.4 \
    && docker-php-ext-enable memcached

# Add Composer files
COPY composer.json composer.loc[k] auth.jso[n] /var/www/

RUN cd /var/www \
    && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --classmap-authoritative

COPY . /var/www

RUN cd /var/www \
    && COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --classmap-authoritative

WORKDIR /var/www

CMD ["php"]