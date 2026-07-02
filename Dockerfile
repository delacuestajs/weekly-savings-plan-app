FROM php:8.2-apache

RUN apt-get update && apt-get install -y tzdata libjpeg62-turbo-dev libpng-dev libfreetype6-dev && \
    ln -sf /usr/share/zoneinfo/America/Bogota /etc/localtime && \
    echo "America/Bogota" > /etc/timezone && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

ENV TZ=America/Bogota

RUN docker-php-ext-configure gd --with-jpeg --with-freetype && \
    docker-php-ext-install pdo pdo_mysql gd exif

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p /var/www/html/uploads && \
    chmod 755 /var/www/html/uploads && \
    a2enmod rewrite && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "error_log = /var/log/php_errors.log" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80
