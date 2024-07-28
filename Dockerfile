# Используем образ с PHP и Apache
FROM php:8.1-apache

RUN apt-get update && \
    apt-get install -y \
        libmemcached-dev \
        zlib1g-dev \
        libz-dev \
        libssl-dev \
        gcc \
        make \
        autoconf \
        libc-dev \
        pkg-config \
        git \
        && rm -rf /var/lib/apt/lists/*

# Устанавливаем необходимые расширения
RUN docker-php-ext-install sockets && \
    pecl install memcached && \
    docker-php-ext-enable memcached

# Копируем файлы нашего приложения в директорию контейнера
COPY var/www/ /var/www/html/

# Открываем порт 80 для веб-сервера
EXPOSE 80