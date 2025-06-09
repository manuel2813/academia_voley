FROM php:8.1-apache

# Instala extensiones necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilita mod_rewrite de Apache
RUN a2enmod rewrite

# Copia los archivos del sistema
COPY . /var/www/html/

# Da permisos correctos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
