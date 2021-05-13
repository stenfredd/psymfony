FROM php:8.0-fpm as psymfonyphp

# Install system dependencies
RUN apt-get update \
    && apt-get install -y \
        curl \
        wget \
        dpkg-dev \
        libpng-dev \
        libjpeg-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
        cron

RUN docker-php-ext-configure gd \
  --enable-gd \
  --with-jpeg

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip

# Create system user
RUN groupadd --gid 1000 appuser
RUN useradd -G www-data,root -s /bin/bash --uid 1000 --gid 1000 appuser

# Symfony
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

USER appuser

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]