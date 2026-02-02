# Stage 1: Node stage
FROM node:20 AS node
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Apache
FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo_mysql zip intl bcmath gd && \
    pecl install redis && docker-php-ext-enable redis && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Apache + storage prep (public not exist yet)
RUN a2enmod rewrite && \
    mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    # Serve the Laravel `public` directory as DocumentRoot (fixes 403 when root has no index)
    sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf && \
    # Suppress Apache ServerName warning
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy code
COPY . .

# Copy Node assets first
COPY --from=node /app/public/build ./public/build

# Post-copy: composer + full permissions
RUN composer install --no-dev --optimize-autoloader && \
    chown -R www-data:www-data storage bootstrap/cache public && \
    chmod -R 775 storage bootstrap/cache public

# Entrypoint
COPY docker /docker
RUN chmod +x /docker/entrypoint.sh