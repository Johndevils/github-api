# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# 1. Install system dependencies and PHP extensions
# We need libcurl for the PHP cURL extension
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Enable Apache mod_rewrite
# (Useful if you add .htaccess rules later)
RUN a2enmod rewrite

# 3. Change Apache Document Root
# By default, Apache looks at /var/www/html. 
# We change this to /var/www/html/public to match your structure.
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy application source code
COPY . /var/www/html

# 6. Set permissions
# Ensure Apache (www-data) owns the files
RUN chown -R www-data:www-data /var/www/html
