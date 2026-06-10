FROM php:8.2-apache

# Instalar las librerías necesarias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

# Copiar el archivo index.php directamente a la carpeta de Apache
COPY index.php /var/www/html/index.php

# Darle permisos correctos a la carpeta
RUN chown -w /var/www/html

EXPOSE 80
