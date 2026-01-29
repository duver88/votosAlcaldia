#!/bin/sh
set -e

# Composer install
cd /var/www/html

# Migrate solo si pendiente (externa)
php artisan migrate

exec php-fpm