FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    libonig-dev \
    mariadb-client \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application
COPY . /var/www/html

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Expose port
EXPOSE 8000

# Run artisan serve
CMD php artisan serve --host=0.0.0.0 --port=8000
