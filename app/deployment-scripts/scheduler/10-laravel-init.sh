#!/bin/bash

cd /app

echo "==============================================="
echo "        Running Laravel Setup Commands"
echo "==============================================="

# CRITICAL: Clear any cached configuration first
echo "Clearing configuration cache..."
php artisan config:clear
php artisan cache:clear

# Verify .env file exists and is readable
if [ ! -f /app/.env ]; then
    echo "ERROR: .env file not found at /app/.env"
    exit 1
fi

echo "✓ .env file exists"

# Show what Laravel actually sees (before our checks)
echo "========== Laravel Environment Debug =========="
php artisan tinker --execute="
    echo 'APP_ENV: ' . env('APP_ENV', 'not set') . '\n';
    echo 'DB_CONNECTION: ' . env('DB_CONNECTION', 'not set') . '\n';
    echo 'DB_HOST: ' . env('DB_HOST', 'not set') . '\n';
    echo 'DB_DATABASE: ' . env('DB_DATABASE', 'not set');
"
echo "==============================================="

# Verify database configuration is set to MariaDB
echo "Verifying database configuration..."
DB_CONNECTION=$(php artisan tinker --execute="echo config('database.default');")

if [ "$DB_CONNECTION" != "mariadb" ]; then
    echo "ERROR: Expected DB_CONNECTION=mariadb, but got: $DB_CONNECTION"
    echo ""
    echo "=== Dumping actual .env file contents ==="
    cat /app/.env | grep -E "DB_CONNECTION|DB_HOST|DB_DATABASE"
    echo "==============================================="
    exit 1
fi

echo "✓ Database driver configured as: mariadb"

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to be ready..."
max_attempts=30
attempt=0

until php artisan db:show 2>&1 | grep -iq "mariadb\|mysql" || [ $attempt -eq $max_attempts ]; do
    attempt=$((attempt + 1))
    echo "Attempt $attempt/$max_attempts: MariaDB not ready yet..."
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "ERROR: Could not connect to MariaDB after $max_attempts attempts"
    echo "=== Database Configuration ==="
    php artisan tinker --execute="
        echo 'Connection: ' . config('database.default');
        echo 'Driver: ' . config('database.connections.mariadb.driver');
        echo 'Host: ' . config('database.connections.mariadb.host');
        echo 'Port: ' . config('database.connections.mariadb.port');
        echo 'Database: ' . config('database.connections.mariadb.database');
        echo 'Username: ' . config('database.connections.mariadb.username');
    "
    echo "=============================="
    exit 1
fi

echo "✓ MariaDB connection successful"

# Verify we're actually connected to MariaDB (not SQLite)
DB_DRIVER=$(php artisan tinker --execute="echo DB::connection()->getDriverName();")

if [ "$DB_DRIVER" != "mariadb" ]; then
    echo "ERROR: Connected to wrong database driver: $DB_DRIVER"
    echo "Expected: mariadb"
    exit 1
fi

echo "✓ Confirmed connected to MariaDB driver"

# Get actual database server version to confirm it's MariaDB
DB_VERSION=$(php artisan tinker --execute="echo DB::select('SELECT VERSION() as version')[0]->version ?? 'unknown';")
echo "Database version: $DB_VERSION"

if echo "$DB_VERSION" | grep -iq "mariadb"; then
    echo "✓ Confirmed MariaDB server"
else
    echo "WARNING: Connected to MySQL driver but server might not be MariaDB"
    echo "Server version: $DB_VERSION"
fi

echo "==============================================="

# Check if APP_KEY is already set and valid
APP_KEY_VALUE=$(php artisan tinker --execute="echo config('app.key');")

if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" == "null" ]; then
    echo "Application Key not found - exiting"
    exit 1
else
    echo "✓ Application key already set"
fi

echo "==============================================="

# Check if migrations are needed
echo "Checking migration status..."

# Get migration status
if php artisan migrate:status 2>&1 | grep -q "Migration table not found"; then
    echo "Migration table doesn't exist - exiting"
    exit 1
elif php artisan migrate:status 2>&1 | grep -q "Pending"; then
    echo "Pending migrations found - exiting"
    exit 1
else
    echo "✓ Database is up to date"
fi

echo "==============================================="

echo "Syncing search indexes..."
# php artisan scout:flush "App\Models\ModelName"
# php artisan scout:import "App\Models\ModelName"

echo "==============================================="

# Optional: Cache optimization
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==============================================="
echo "        ✓ Laravel Setup Complete"
echo "==============================================="