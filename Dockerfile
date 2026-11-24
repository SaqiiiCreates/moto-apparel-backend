
FROM node:20-bookworm AS assets

WORKDIR /app
COPY . .

# Build Laravel, Bagisto Admin and Shop assets
RUN npm install && npm run build
RUN cd packages/Webkul/Admin && npm install && npm run build
RUN cd packages/Webkul/Shop && npm install --ignore-scripts && npm run build

FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    calendar intl mbstring pdo pdo_mysql gd zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY ca.pem /etc/ssl/certs/aiven-ca.pem

RUN git config --global --add safe.directory /var/www/html \
    && composer install --no-dev --prefer-dist \
    --optimize-autoloader --no-interaction

COPY --from=assets /app/public/ ./public/

RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-80}"]
