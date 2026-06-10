FROM php:8.2-apache

# Instalar dependencias del sistema para Postgres y drivers
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Copiar el código al directorio del servidor
COPY . /var/www/html/

# Dar permisos
RUN chown -R www-data:www-data /var/www/html
