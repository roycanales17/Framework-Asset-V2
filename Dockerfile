# Use an official PHP image with Apache
FROM php:8.2-apache

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/htaccess.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/htaccess.conf \
    && a2enconf htaccess

# Install necessary extensions, including Memcached
RUN apt-get update && apt-get install -y \
    libmemcached-dev \
    && pecl install memcached \
    && docker-php-ext-enable memcached

# Copy your project into the container's web directory
COPY . /var/www/html

# Expose port 80
EXPOSE 80

# Set the working directory (optional)
WORKDIR /var/www/html