FROM composer:2 AS composer

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        git \
        unzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl opcache pcntl pdo_mysql zip \
    && pecl install redis pcov \
    && docker-php-ext-enable redis pcov \
    && pecl clear-cache \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

ARG XDEBUG=false

RUN if [ "$XDEBUG" = true ]; then \
        apk add --no-cache --virtual .xdebug-deps $PHPIZE_DEPS linux-headers \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && apk del .xdebug-deps \
        && printf 'xdebug.mode=develop,debug,coverage\nxdebug.client_host=host.docker.internal\nxdebug.start_with_request=trigger\n' > /usr/local/etc/php/conf.d/xdebug.ini; \
    fi

COPY ./conf.d/* /usr/local/etc/php/conf.d/

ARG UID=1000
ARG GID=1000

RUN deluser --remove-home www-data \
    && addgroup -g "$GID" www-data \
    && adduser -S -G www-data -u "$UID" -h /home/www-data www-data \
    && mkdir -p /home/www-data/.composer/cache \
    && chown -R www-data:www-data /home/www-data

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_HOME=/home/www-data/.composer

USER www-data

WORKDIR /var/www/portfolio/october-sandbox

EXPOSE 9000

CMD ["php-fpm"]
