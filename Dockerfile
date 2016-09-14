FROM php:7.0-apache
RUN a2enmod rewrite

COPY vendor/ /var/www/html/vendor
COPY src/ /var/www/html/
