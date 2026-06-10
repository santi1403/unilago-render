FROM php:8.2-apache

# Instalar dependencias necesarias para las bases de datos
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Habilitar el módulo rewrite de Apache
RUN a2enmod rewrite

# Copiar el código fuente a la carpeta del servidor
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
