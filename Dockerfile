# Usa una imagen de PHP con Apache
FROM php:8.2-apache

# Habilita módulos necesarios
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia los archivos del proyecto al contenedor
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Ejecutar Apache en modo foreground
<<<<<<< HEAD
CMD ["apache2-foreground"]
=======
CMD ["apache2-foreground"]
>>>>>>> bbacd166ba8288474b11007139228e37852ed2d8
