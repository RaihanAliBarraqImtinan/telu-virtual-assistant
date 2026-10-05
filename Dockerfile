FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan CodeIgniter 4
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install intl zip pdo pdo_mysql

# Enable Apache mod_rewrite untuk routing CodeIgniter
RUN a2enmod rewrite

# Ubah DocumentRoot Apache ke folder public CodeIgniter
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy project ke container
COPY . /var/www/html

# Set permission folder writable
RUN chown -R www-data:www-data /var/www/html/writable

EXPOSE 80