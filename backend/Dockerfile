FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers

WORKDIR /var/www/html

COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/petron-entrypoint
COPY controller/ ./controller/
COPY database/ ./database/
COPY includes/ ./includes/
COPY model/ ./model/
COPY public/ ./public/
COPY index.php ./

RUN chown -R www-data:www-data /var/www/html \
    && chmod +x /usr/local/bin/petron-entrypoint

EXPOSE 8080

CMD ["petron-entrypoint"]
