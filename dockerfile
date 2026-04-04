# Base PHP
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working dir
WORKDIR /var/www

# Copy semua file
COPY . .

# Install Laravel deps
RUN composer install --no-dev --optimize-autoloader

# Build frontend (Livewire + Vite)
RUN npm install && npm run build

# Permission
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Port
EXPOSE 80

# Run
CMD service nginx start && php-fpm