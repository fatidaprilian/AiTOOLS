# Gunakan image dasar PHP 8.2 dengan Apache
FROM php:8.2-apache

# Install dependensi sistem yang umum dibutuhkan
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang dibutuhkan oleh Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Aktifkan modul rewrite Apache untuk URL cantik Laravel
RUN a2enmod rewrite

# Salin file konfigurasi Virtual Host kustom kita untuk Apache
# Ini adalah pengganti perintah 'sed' yang kita hapus
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Tentukan direktori kerja di dalam kontainer
WORKDIR /var/www/html

# Install Composer (manajer paket PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin file composer terlebih dahulu untuk memanfaatkan cache Docker
COPY composer.json composer.lock ./

# Install dependensi PHP tanpa paket development
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Salin semua file aplikasi Laravel
COPY . .

# Buat autoload yang dioptimalkan untuk produksi
RUN composer dump-autoload --no-dev --optimize

# Atur izin file dan folder agar bisa ditulis oleh server
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# Perintah final untuk memulai server
# Mengubah port Listen Apache ke port yang diberikan oleh Railway, lalu memulai server
CMD sed -i -e "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf && apache2-foreground