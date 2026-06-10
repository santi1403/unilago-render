FROM php:8.2-apache

# 1. Instalar dependencias del sistema y herramientas de compilación
RUN apt-get update && apt-get install -y \
    libssl-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar y habilitar la extensión oficial de MongoDB para PHP
RUN pecl install mongodb && docker-php-ext-enable mongodb

# 3. Instalar Composer (El gestor de paquetes de PHP) de forma global
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. Establecer el directorio de trabajo en la carpeta del servidor Apache
WORKDIR /var/www/html

# 5. Copiar los archivos de tu repositorio al contenedor
COPY . /var/www/html/

# 6. Ejecutar Composer para instalar el driver de MongoDB y generar el 'vendor/autoload.php'
RUN composer require mongodb/mongodb --no-interaction

# 7. Asegurar los permisos correctos usando el usuario de Apache (CORREGIDO)
RUN chown -R www-data:www-data /var/www/html

# 8. Exponer el puerto estándar de Apache
EXPOSE 80
