FROM php:8.2-fpm

# Устанавливаем дополнительные пакеты и зависимости
RUN apt-get update  \
    && apt-get install -y libpq-dev

# Установка Xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install pdo_pgsql

# Настройка Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini