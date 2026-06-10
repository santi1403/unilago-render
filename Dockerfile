FROM php:8.2-apache

# Instalar las herramientas necesarias y la extensión nativa de MongoDB
RUN apt-get update && apt-get install -null -y \
    libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Copiar los archivos de tu proyecto al servidor
COPY . /var/www/html/

# Exponer el puerto estándar
EXPOSE 80
