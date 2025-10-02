# Use the official FrankenPHP image
FROM dunglas/frankenphp:latest

# Set working directory
WORKDIR /app

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        bz2 \
        gd \
        intl \
        mbstring \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        soap \
    && docker-php-ext-enable opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear PHP opcache
RUN echo "opcache.enable_cli=0" > /usr/local/etc/php/conf.d/opcache-disable-cli.ini

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

# Copy application code
COPY . .

# Create .env file if it doesn't exist and set proper permissions
RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Generate application key only if .env exists
RUN if [ -f .env ]; then php artisan key:generate; fi

# Build frontend assets
RUN npm install
RUN npm run build

# Optimize Laravel for production (only if .env exists and is not example)
RUN if [ -f .env ] && [ ! .env -ef .env.example ]; then \
    php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache; \
    fi

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/app/caddy/Caddyfile"]