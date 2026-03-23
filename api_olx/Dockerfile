FROM php:7.4-fpm

RUN apt-get update \
     && apt-get install -y libzip-dev \
     && docker-php-ext-install zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | \
            php -- --install-dir=/usr/bin/ --filename=composer

# Copy new contents
WORKDIR /var/www/html
COPY . .

# Install Composer dependencies without autoloader
RUN composer install

RUN composer update

EXPOSE 80

# Run Local Development Server
CMD ["php", "-S", "0.0.0.0:80"]