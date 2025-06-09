FROM php:8.1-apache

# Copia todo el contenido del repo al contenedor
COPY . /var/www/html/

# Habilita mod_rewrite (útil para sistemas como el tuyo)
RUN a2enmod rewrite

# Exponer el puerto 80
EXPOSE 80
