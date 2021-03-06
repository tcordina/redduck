ARG COMPOSER_VERSION=1.9.0
ARG PHP_VERSION=7.3.9
ARG APCU_VERSION=5.1.17


#####################################
##               BASE              ##
#####################################
FROM php:${PHP_VERSION}-fpm-alpine as base

ARG APCU_VERSION

EXPOSE 80

# Install paquet requirements
RUN export PHP_CPPFLAGS="${PHP_CPPFLAGS} -std=c++11"; \
    set -ex; \
    # Install required system packages
    apk add --no-cache \
        nginx \
        supervisor \
    ; \
    apk add --no-cache --virtual build-dependencies \
        autoconf \
        gcc \
        libc-dev \
        libzip-dev \
        make \
        pkgconfig \
    ; \
    #Install the PHP extensions
    docker-php-ext-install -j "$(nproc)" \
        bcmath \
        pdo \
        pdo_mysql \
        zip \
    ; \
    pecl install \
        apcu-${APCU_VERSION} \
    ; \
    docker-php-ext-enable \
        opcache \
        apcu \
    ; \
    docker-php-source delete; \
    apk del build-dependencies; \
    # Clean tmp directory
    rm -rf /tmp/* /var/tmp/*; \
    ## set recommended PHP.ini settings
    { \
        echo 'date.timezone = Europe/Paris'; \
        echo 'short_open_tag = off'; \
        echo 'expose_php = off'; \
        echo 'error_log = /proc/self/fd/2'; \
        echo 'memory_limit = 128m'; \
        echo 'post_max_size = 110m'; \
        echo 'upload_max_filesize = 100m'; \
        echo 'opcache.enable = 1'; \
        echo 'opcache.enable_cli = 1'; \
        echo 'opcache.memory_consumption = 256'; \
        echo 'opcache.interned_strings_buffer = 16'; \
        echo 'opcache.max_accelerated_files = 20011'; \
        echo 'opcache.fast_shutdown = 1'; \
        echo 'opcache.validate_timestamps = 0'; \
        echo 'realpath_cache_size = 4096K'; \
        echo 'realpath_cache_ttl = 600'; \
        echo 'session.name = JSESSIONID'; \
    } > /usr/local/etc/php/php.ini; \
    { \
        echo 'date.timezone = Europe/Paris'; \
        echo 'short_open_tag = off'; \
        echo 'memory_limit = -1'; \
    } > /usr/local/etc/php/php-cli.ini; \
    ## create dir to avoid nginx error
    mkdir /var/tmp/nginx;

# copy the Nginx config
COPY docker/nginx.conf /etc/nginx/

# copy the Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/


#####################################
##               APP               ##
#####################################
FROM base as app

ARG COMPOSER_VERSION

ENV APP_ENV=prod

COPY --chown=www-data . /app
WORKDIR /app

# Install Composer
RUN set -ex; \
    apk add --no-cache curl; \
    curl -L -o composer.phar https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar; \
    chmod +x composer.phar && mv composer.phar /usr/local/bin/composer; \
    composer install -o --no-ansi --no-dev; \
    apk del curl;

# copy the Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN ["chmod", "+x", "/usr/local/bin/entrypoint.sh"]

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]