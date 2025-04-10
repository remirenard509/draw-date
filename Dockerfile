FROM php:8.1-apache

# Mettre à jour apt et installer les dépendances nécessaires pour mysqli et xdebug
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    ca-certificates \
    libx11-dev \
    unzip \
    && docker-php-ext-install mysqli \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers de l'application dans Apache
COPY ./app/ /var/www/html/app/
COPY .env /var/www/html/.env
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf


# Ajouter la configuration de Xdebug
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
# Copier composer.json et composer.lock
COPY ./composer.json ./composer.lock /var/www/html/

# Appliquer les permissions après avoir copié les fichiers
RUN chmod -R 755 /var/www/html/

# Définir le répertoire de travail pour composer
WORKDIR /var/www/html/

RUN a2ensite 000-default.conf
RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql

# Installer les dépendances avec Composer si composer.json existe
RUN composer install

# Exposer le port 80 pour accéder à l'application
EXPOSE 80