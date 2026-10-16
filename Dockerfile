FROM php:8.5-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    libonig-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo_sqlite \
        mbstring \
        bcmath \
        exif \
        gd \
        pcntl \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html