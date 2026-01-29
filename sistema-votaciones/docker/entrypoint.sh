#!/bin/sh
set -e

# Composer install
cd /var/www/html

# Migrate solo si pendiente (externa)
php artisan migrate
php artisan db:seed

exec php-fpm