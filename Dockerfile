# Sử dụng PHP với Apache
FROM php:8.1-apache

# Cài extension cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy project vào container
COPY . /var/www/html

# Set quyền cho thư mục storage và bootstrap
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy file vhost config nếu cần (nếu dùng Laravel public folder)
# COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf

# Cài composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Cài thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80
