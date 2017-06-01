FROM php:7-apache

RUN docker-php-ext-install sockets
RUN rm -rf /var/www/*
ADD . /var/www/
WORKDIR /var/www
RUN rm -rf html && ln -s /var/www/web html
