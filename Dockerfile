# Usa PHP con Apache
FROM php:8.2-apache

# Instala dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev pkg-config libssl-dev zip unzip git \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Copia los archivos del proyecto al contenedor
COPY . /var/www/html/

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala las dependencias PHP (incluye openai-php/client)
WORKDIR /var/www/html/
RUN composer install --no-dev --optimize-autoloader

# Da permisos al servidor
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto
EXPOSE 80

# Inicia Apache
CMD ["apache2-foreground"]
