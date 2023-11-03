FROM php:8.2-fpm

# Установка Xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Настройка Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini