FROM php:8.1-apache

# Copier Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier la configuration Apache
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./composer.json /var/www/html/composer.json
COPY ./composer.lock /var/www/html/composer.lock
RUN echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini



# Définir le répertoire de travail
WORKDIR /var/www/html/

# Activer les modules Apache nécessaires
RUN a2ensite 000-default.conf
RUN a2enmod rewrite

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Installer les dépendances avec Composer
RUN composer install
