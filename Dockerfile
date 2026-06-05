FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        default-mysql-client \
        libjpeg-dev libpng-dev libwebp-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

# Force a clean MPM state: remove every mpm_* symlink, then enable only prefork.
# apt occasionally re-enables mpm_event during dependency installs, which
# conflicts with PHP's mod_php (requires prefork) -> AH00534.
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork rewrite \
    && ls /etc/apache2/mods-enabled/ | grep mpm

COPY . /var/www/html/

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080
ENV PORT=8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
