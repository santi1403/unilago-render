FROM php:8.2-apache

# Actualizar y corregir el comando de instalación (sin el error '-null')
RUN apt-get update && apt-get install -y \
    libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Copiar los archivos de tu proyecto al servidor
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80
