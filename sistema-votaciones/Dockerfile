# Stage 1: Node stage
FROM node:20 AS node
WORKDIR /app

COPY package*.json ./
RUN npm ci  # Todas deps

COPY . .

RUN npm run build


# Stage 2: PHP (main)
FROM php:8.2-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libonig-dev \
        libicu-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libpq-dev \
        libssl-dev \
        curl \
        zip \
        npm && \
    docker-php-ext-configure zip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        intl \
        sockets \
        opcache \
        bcmath \
        gd && \
    pecl install redis && docker-php-ext-enable redis && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php && \
    npm install -g npm@latest && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

RUN groupadd -g 1000 appuser && \
    useradd -u 1000 -g appuser -m appuser

USER root
COPY --chown=appuser:appuser docker /docker
RUN chmod +x /docker/entrypoint.sh

COPY --from=node /app/public/build ./public/build

COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache --no-interaction && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan key:generate --no-interaction

USER appuser