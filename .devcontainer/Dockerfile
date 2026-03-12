FROM mcr.microsoft.com/devcontainers/php:1-8.3-bookworm

# -----------------------------------------------------------------------------
#  Fix yarn signature issue, open bug with microsoft images
#   https://github.com/devcontainers/images/issues/1752
# -----------------------------------------------------------------------------
RUN curl -fsSL https://dl.yarnpkg.com/debian/pubkey.gpg \
  | sudo gpg \
        --batch \
        --yes \
        --dearmor \
        -o /usr/share/keyrings/yarn-archive-keyring.gpg

# Install Debian Packages
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
        git \
        gcc \
        make \
        zip \
        unzip \
        libnss3-tools \
        gnupg \
        mariadb-client \
        build-essential \
        autoconf \
        pkg-config \
        libtool \
        re2c \
        libxml2-dev \
        libssl-dev \
        libcurl4-openssl-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libicu-dev \
        zlib1g-dev \
        libzip-dev \
        unixodbc-dev \
        libkrb5-dev \
        libgmp-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Bun
RUN curl -fsSL https://bun.sh/install | bash && \
    mv ~/.bun/bin/bun /usr/local/bin/bun && \
    ln -s /usr/local/bin/bun /usr/local/bin/bunx

# Install php-mysql driver and other extensions (except redis)
RUN docker-php-ext-install \
    pdo_mysql \
    gd \
    intl \
    zip

# Install Composer
COPY --from=docker.io/composer:latest /usr/bin/composer /usr/local/bin/composer

# Install PIE: https://github.com/php/pie
RUN curl -L --output /usr/bin/pie https://github.com/php/pie/releases/download/1.3.8/pie.phar && \
    chmod +x /usr/bin/pie

# Copy php Config:
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

# Install redis extensions via PECL
RUN pie install phpredis/phpredis && \
    pie install xdebug/xdebug && \
    docker-php-ext-enable redis xdebug

# Configure XDEBUG
RUN mkdir -p $PHP_INI_DIR/conf.d && \
    echo "xdebug.mode=debug" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.discover_client_host=1" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.client_port=9003" >> $PHP_INI_DIR/conf.d/xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log" >> $PHP_INI_DIR/conf.d/xdebug.ini