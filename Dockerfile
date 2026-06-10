# Usamos la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Actualizamos el sistema e instalamos herramientas para Postgres y MongoDB
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libssl-dev \
    openssl \
    && rm -rf /var/lib/apt/lists/*

# Instalamos la extensión PDO para PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Instalamos la extensión de MongoDB para PHP usando PECL
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Habilitamos el módulo de reescritura de Apache por si acaso
RUN a2enmod rewrite

# Exponemos el puerto estándar
EXPOSE 80
