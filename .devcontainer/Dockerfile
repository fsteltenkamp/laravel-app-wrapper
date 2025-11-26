FROM mcr.microsoft.com/devcontainers/php:1-8.3-bookworm

# Install Debian Packages
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
        git \
        zip \
        unzip \
        libnss3-tools \
        gnupg \
        mariadb-client \
        git-flow \
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

# Install redis extensions via PECL
RUN pecl install redis xdebug && \
    docker-php-ext-enable redis xdebug

# Install Composer
COPY --from=docker.io/composer:latest /usr/bin/composer /usr/local/bin/composer

# PHP config
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && \
    echo "\n[xdebug]\nxdebug.mode=disabled\nxdebug.start_with_request=yes\nxdebug.client_host=172.17.0.1\nxdebug.client_port=9000\n" >> $PHP_INI_DIR/php.ini

# Set up SSH Keys:
COPY --chown=1000:1000 ssh/id_rsa /home/vscode/.ssh/id_rsa
COPY --chown=1000:1000 ssh/id_rsa.pub /home/vscode/.ssh/id_rsa.pub
RUN chmod -R 700 /home/vscode/.ssh
RUN ssh-keyscan -t rsa github.com >> /home/vscode/.ssh/known_hosts