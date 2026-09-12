#!/bin/bash
set -e

# Install composer dependencies
composer install --no-interaction --optimize-autoloader

# Set directory permissions
echo ""
echo "Creating cache folders and setting permissions"
mkdir -p /application/bootstrap/cache \
         /application/storage/framework/cache \
         /application/storage/framework/cache/data \
         /application/storage/framework/sessions \
         /application/storage/framework/views \
         /application/storage/debugbar \
         /application/storage/clockwork
chown -R nginx:nginx /application/storage /application/bootstrap/cache
chmod -R 777 /application/storage /application/bootstrap/cache

# Execute the passed command
exec "$@"
