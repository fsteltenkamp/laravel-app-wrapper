#!/bin/bash

cd /app

echo "================================================"

echo "          Creating .env file"
rm .env
cp .env.example .env
echo "==============================================="

if [ -f artisan ]; then
    echo "        Running Laravel Setup Commands"
    php artisan down
    php artisan key:generate
    php artisan migrate --force
    # Insert scout:flush, scout:index and scout:import commands here for each searchable model:
    # scout:flush "App\Models\ModelName"
    # scout:index "App\Models\ModelName"
    # scout:import "App\Models\ModelName"
    
    echo "==============================================="

    php artisan up
    echo "        Laravel Setup Complete"
    echo "==============================================="
fi