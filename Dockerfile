# Utiliser une image PHP avec Apache
FROM php:8.2-apache

# Installer les dépendances système pour PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP nécessaires (MySQL et PostgreSQL)
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Activer mod_rewrite et mod_headers
RUN a2enmod rewrite headers

# Définir DocumentRoot correctement
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copier les fichiers de l'application dans le conteneur
COPY . /var/www/html/

# Définir le répertoire de travail
WORKDIR /var/www/html

# Permissions correctes pour uploads
RUN mkdir -p /var/www/html/uploads/messages/images \
    && mkdir -p /var/www/html/uploads/messages/videos \
    && chmod -R 777 /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

# Exposition du port 80
EXPOSE 80

# Commande par défaut
CMD ["apache2-foreground"]
