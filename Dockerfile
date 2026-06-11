FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p /var/www/html/uploads && \
    chmod 755 /var/www/html/uploads && \
    a2enmod rewrite

EXPOSE 80
