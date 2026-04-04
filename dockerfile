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
    net-tools \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Permission
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# 🔥 PENTING: expose port
EXPOSE 80

# 🔥 RUN 2 SERVICE SEKALIGUS
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"