FROM php:8.2-apache

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/Key \
    && chmod -R 755 /var/www/html/Key

EXPOSE 80
