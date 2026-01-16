#!/bin/sh
set -e

# Fix permissions for var directory
if [ -d /var/www/html/var ]; then
    chown -R www-data:www-data /var/www/html/var
    chmod -R 775 /var/www/html/var
fi

# Create var directories if they don't exist
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

# Execute the main command (php-fpm runs as root and manages www-data pool)
exec "$@"
