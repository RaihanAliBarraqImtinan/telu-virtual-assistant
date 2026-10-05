FROM php:8.2-fpm

# Install dependensi dan extension intl
RUN apt-get update && apt-get install -y \
    libicu-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

WORKDIR /var/www/html