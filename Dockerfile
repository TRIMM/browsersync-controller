FROM php:7-apache

RUN apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
  unzip
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN docker-php-ext-install sockets
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer
WORKDIR /var/www
COPY composer.json ./
COPY composer.lock ./
RUN composer install --no-scripts --no-autoloader
COPY . ./
RUN composer dump-autoload --optimize && \
    composer run-script post-install-cmd
RUN chown -R www-data:www-data .
RUN rm -rf html && ln -s /var/www/web html
