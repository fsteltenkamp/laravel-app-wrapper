# Same PHP build as production (app/Dockerfile), plus development tooling.
# Microsoft's devcontainers/php images lag behind PHP releases; basing the dev
# container on the production image keeps PHP versions and extensions identical.
FROM docker.io/dunglas/frankenphp:php8.5-trixie

# Install Debian Packages
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
        git \
        zip \
        unzip \
        less \
        sudo \
        libnss3-tools \
        gnupg \
        mariadb-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Bun
RUN curl -fsSL https://bun.sh/install | bash && \
    mv ~/.bun/bin/bun /usr/local/bin/bun && \
    ln -s /usr/local/bin/bun /usr/local/bin/bunx

# Same extensions as production, plus xdebug
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    redis \
    xdebug

# Install Composer
COPY --from=docker.io/composer:2 /usr/bin/composer /usr/local/bin/composer

# Development PHP config
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

# Configure XDEBUG
RUN mkdir -p $PHP_INI_DIR/conf.d && \
    echo "xdebug.mode=debug" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.discover_client_host=1" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.client_port=9003" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log" >> $PHP_INI_DIR/conf.d/xdebug.ini
