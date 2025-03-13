FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

# ⚠️ Không chạy scripts khi chưa đủ source code
RUN composer install --optimize-autoloader --no-dev --no-scripts

COPY . .

# ✅ Giờ chạy script sau khi đã có đủ source code
RUN composer run-script post-autoload-dump || true
RUN php artisan config:clear

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
