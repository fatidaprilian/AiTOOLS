# Tahap 1: Install dependensi PHP dengan Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# 
# GANTI <SERVICE_ID_ANDA> DI BAWAH INI DENGAN SERVICE ID ANDA DARI RAILWAY
# 
RUN --mount=type=cache,id=s/<SERVICE_ID_ANDA>-/root/.composer/cache,target=/root/.composer/cache \
    composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev

# Tahap 2: Setup image utama dengan Nginx dan PHP-FPM
FROM richarvey/nginx-php-fpm:latest

# Instal ekstensi PHP yang umum dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Salin file konfigurasi Nginx yang sudah kita buat
COPY nginx.conf /etc/nginx/sites-available/default.conf

# Set working directory
WORKDIR /var/www/html

# Salin file vendor dari tahap pertama
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Salin semua sisa file aplikasi Laravel
COPY . /var/www/html/

# Atur kepemilikan dan izin folder yang benar untuk Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80