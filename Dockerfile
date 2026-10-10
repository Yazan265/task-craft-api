# ---- Dockerfile لمشروع TaskCraft API (Laravel 11 / PHP 8.2) ----

FROM php:8.2-fpm-bookworm

# تثبيت الاعتمادات النظامية ومكتبات PHP المطلوبة لـ Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# تحسين الكاش: نسخ ملفات الاعتمادات أولاً
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader

# نسخ باقي ملفات المشروع
COPY . .

# تفريغ الكاش المتبقي في الكود لتجنب البحث عن حزم الـ dev
RUN php artisan config:clear || true \
    && php artisan event:clear || true

# إكمال تثبيت composer مع تحسين الـ Autoloader وتعطيل السكريبتات أثناء البناء
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ضبط صلاحيات مجلدات Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]