FROM php:8.2-apache

# Install Tesseract and dependencies
RUN apt-get update && \
    apt-get install -y \
    tesseract-ocr \
    libpng-dev \
    libonig-dev \
    unzip && \
    docker-php-ext-install gd mbstring && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

COPY composer.json composer.lock ./
RUN composer install
COPY . .

# Install PHP dependencies inside the container
RUN composer install

# Run composer with verbose output to see the actual error
RUN composer install --no-interaction --prefer-dist --verbose

# Set working directory
WORKDIR /var/www/html

# Copy app files
COPY . .

# Set correct permissions for Apache (www-data) user
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 775 /var/www/html/uploads

EXPOSE 80



