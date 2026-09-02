#!/bin/sh
set -e

# Replace Apache port with Render PORT (default to 80 if not set)
PORT=${PORT:-80}
sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Cache configuration, routes, and views in production
echo "Running cache..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force || true

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
