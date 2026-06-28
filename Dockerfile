FROM php:8.2-apache

# Install mysqli and pdo_mysql extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application code into the Apache web root
COPY ./bpms/ /var/www/html/

# Ensure permissions are correct for the web server
RUN chown -R www-data:www-data /var/www/html
