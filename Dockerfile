# Use an official PHP 8.0 image with Apache
FROM php:8.0-apache

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Install necessary dependencies for Memcached, PDO, and mysqli
RUN apt-get update && apt-get install -y \
    libmemcached-dev \
    zlib1g-dev \
    libssl-dev \
    libzstd-dev \
    build-essential \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/htaccess.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/htaccess.conf \
    && a2enconf htaccess

# Set session save path
RUN echo "session.save_path = /var/lib/php/sessions" > /usr/local/etc/php/conf.d/session.ini

# Copy your project into the container's web directory
COPY ./ /var/www/html

# Expose port 80
EXPOSE 80

# Set the working directory (optional)
WORKDIR /var/www/html