#!/bin/sh
set -e

# Port configuration for Render (Render passes $PORT, e.g. 10000)
PORT="${PORT:-8080}"
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Create storage symlink if it doesn't exist
php artisan storage:link || true

# Run migrations if enabled in environment variables
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

# Run seeder if enabled in environment variables
if [ "$RUN_SEEDER" = "true" ]; then
    echo "Running database seeder..."
    php artisan db:seed --force || true
fi

# Optimize Laravel for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Gatherly is starting on port ${PORT}..."
exec apache2-foreground
