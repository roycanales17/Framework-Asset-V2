# Use an official PHP 8.2 image with Apache
FROM php:8.2-apache

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Update and install required dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libbz2-dev libcurl4-nss-dev libxml2-dev libssl-dev libpng-dev libc-client-dev libkrb5-dev libxslt1-dev libzip-dev libonig-dev \
    libmemcached-dev libssh2-1-dev libmcrypt-dev \
    libwebp-dev libjpeg62-turbo-dev libxpm-dev libfreetype6-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-xpm --with-webp --enable-gd \
    && docker-php-ext-install gd

# IMAP extension
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

# Install necessary PHP extensions
RUN docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql bcmath bz2 calendar curl dom exif ftp gettext iconv intl mbstring opcache soap shmop sockets sysvmsg sysvsem sysvshm xsl zip

# Install PECL extensions
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install igbinary \
    && docker-php-ext-enable igbinary

RUN pecl install msgpack \
    && docker-php-ext-enable msgpack

RUN pecl install mcrypt \
    && docker-php-ext-enable mcrypt

# Memcached extension
RUN pecl install memcached --with-libmemcached-dir=/usr \
    && docker-php-ext-enable memcached

# SSH2 extension
RUN pecl install ssh2 \
    && docker-php-ext-enable ssh2

# Clean up
RUN docker-php-source delete

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
