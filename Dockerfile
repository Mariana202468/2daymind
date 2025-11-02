# Etapa base
FROM php:8.2-apache

# Instala dependencias necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install zip pdo pdo_mysql

# Copia todos los archivos al servidor web
COPY . /var/www/html/

# Habilita mod_rewrite (importante para PHP moderno)
RUN a2enmod rewrite

# Cambia permisos
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto estándar
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
