#!/bin/sh
set -e

# Composer install
cd /var/www/html

# Now safe
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate

php artisan storage:link

exec apache2-foreground
