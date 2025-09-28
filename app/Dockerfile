# Base stage with common components
FROM docker.io/dunglas/frankenphp:php8.3-bookworm AS base

# Metadata
LABEL org.opencontainers.image.source="https://github.com/fsteltenkamp/laravel-app-base"

# Install common packages
RUN apt-get update && \
    apt-get -y --no-install-recommends install \
        git \
        zip \
        unzip \
        libnss3-tools \
        gnupg \
    && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install Bun
RUN curl -fsSL https://bun.sh/install | bash && \
    mv ~/.bun/bin/bun /usr/local/bin/bun && \
    ln -s /usr/local/bin/bun /usr/local/bin/bunx

# add additional extensions here:
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    redis

# Install Composer
COPY --from=docker.io/composer:latest /usr/bin/composer /usr/local/bin/composer

# Production PHP config
RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

# Copy application files
COPY ./src /app
COPY ./Caddyfile /etc/caddy/Caddyfile

# Install dependencies and build
RUN cd /app && \
    composer install --no-dev --optimize-autoloader && \
    bun install --prod

# App image
FROM base AS app

LABEL org.opencontainers.image.title="Laravel App Base"
LABEL org.opencontainers.image.description="Laravel App Base"

# Create directories for initialization scripts
RUN mkdir -p /docker-entrypoint-init.d \
    && mkdir -p /docker-entrypoint-init.logs \
    && mkdir -p /docker-entrypoint-init.workdir

# Copy entrypoint and initialization scripts
COPY ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY ./deployment-scripts/* /docker-entrypoint-init.d/

# Set permissions for scripts
RUN chmod +x /docker-entrypoint-init.d/* \
    && chmod +x /usr/local/bin/docker-entrypoint

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Entrypoint Script
ENTRYPOINT ["docker-entrypoint"]

# Worker image
FROM base AS worker

LABEL org.opencontainers.image.title="Laravel App Base Worker"
LABEL org.opencontainers.image.description="Laravel App Base Worker"

# No entrypoint - will be set via docker-compose.yml command