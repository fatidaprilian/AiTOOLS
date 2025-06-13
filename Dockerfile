# Gunakan base image yang sudah mencakup Nginx dan PHP-FPM
FROM richarvey/nginx-php-fpm:2.2.0

# Salin file composer terlebih dahulu untuk caching
COPY composer.json composer.lock /var/www/html/

# Tentukan direktori kerja
WORKDIR /var/www/html/

# Install dependensi composer tanpa paket development
RUN composer install --no-interaction --no-plugins --no-scripts --no-dev --prefer-dist

# Salin seluruh file aplikasi
COPY . /var/www/html

# Atur kepemilikan dan izin folder agar bisa ditulis oleh web server
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80 yang digunakan oleh Nginx
EXPOSE 80