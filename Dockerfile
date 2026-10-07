FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache nginx supervisor git unzip libpng-dev libzip-dev zip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

# Config permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=80"]