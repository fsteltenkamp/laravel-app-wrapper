# Base stage with common components
FROM docker.io/dunglas/frankenphp:php8.5-bookworm AS base

# Metadata
LABEL org.opencontainers.image.licenses="ARR"
LABEL org.opencontainers.image.source="CHANGEME"
LABEL org.opencontainers.image.url="CHANGEME"
LABEL org.opencontainers.image.vendor="CHANGEME"
LABEL org.opencontainers.image.version="CHANGEME"

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

# Dev Dependencies & Temporary files cleanup
RUN php artisan view:clear && \
    php artisan config:clear && \
    php artisan route:clear && \
    rm -rf /app/vendor && \
    rm -rf /app/node_modules && \
    rm -rf /app/.env && \
    rm -rf /app/public/build && \
    rm -rf /app/public/hot && \
    rm -rf /app/storage/app/documents && \
    rm -rf /app/storage/private/crash-reports
# Install dependencies and build
RUN cd /app && \
    composer install --no-dev --optimize-autoloader && \
    bun install --prod && \
    bun run build && \
    php artisan config:cache && \
    php artisan view:cache && \
    php artisan route:cache

# App image
FROM base AS app

LABEL org.opencontainers.image.title="Laravel App"
LABEL org.opencontainers.image.description="Laravel Application Container"

# Create directories for initialization scripts
RUN mkdir -p /docker-entrypoint-init.d \
    && mkdir -p /docker-entrypoint-init.logs \
    && mkdir -p /docker-entrypoint-init.workdir

# Copy entrypoint and initialization scripts
COPY ./entrypoint-scripts/entrypoint-app.sh /usr/local/bin/docker-entrypoint
COPY ./deployment-scripts/shared/* /docker-entrypoint-init.d/
COPY ./deployment-scripts/app/* /docker-entrypoint-init.d/

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

LABEL org.opencontainers.image.title="Laravel Worker"
LABEL org.opencontainers.image.description="Laravel Worker"

# Create directories for initialization scripts
RUN mkdir -p /docker-entrypoint-init.d \
    && mkdir -p /docker-entrypoint-init.logs \
    && mkdir -p /docker-entrypoint-init.workdir

# Copy entrypoint and initialization scripts
COPY ./entrypoint-scripts/entrypoint-worker.sh /usr/local/bin/docker-entrypoint
COPY ./deployment-scripts/shared/* /docker-entrypoint-init.d/
COPY ./deployment-scripts/worker/* /docker-entrypoint-init.d/

# Set permissions for scripts
RUN chmod +x /docker-entrypoint-init.d/* \
    && chmod +x /usr/local/bin/docker-entrypoint

# Start Entrypoint Script
ENTRYPOINT ["docker-entrypoint"]

# Scheduler image
FROM base AS scheduler

LABEL org.opencontainers.image.title="Laravel Scheduler"
LABEL org.opencontainers.image.description="Laravel Scheduler"

# Create directories for initialization scripts
RUN mkdir -p /docker-entrypoint-init.d \
    && mkdir -p /docker-entrypoint-init.logs \
    && mkdir -p /docker-entrypoint-init.workdir

# Copy entrypoint and initialization scripts
COPY ./entrypoint-scripts/entrypoint-scheduler.sh /usr/local/bin/docker-entrypoint
COPY ./deployment-scripts/shared/* /docker-entrypoint-init.d/
COPY ./deployment-scripts/scheduler/* /docker-entrypoint-init.d/

# Set permissions for scripts
RUN chmod +x /docker-entrypoint-init.d/* \
    && chmod +x /usr/local/bin/docker-entrypoint

# Start Entrypoint Script
ENTRYPOINT ["docker-entrypoint"]