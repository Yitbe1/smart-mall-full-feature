# =============================================================================
# Smart Mall — Dockerfile
# Base: php:8.2-apache
# =============================================================================

FROM php:8.2-apache

LABEL maintainer="Smart Mall Team"
LABEL description="Smart Mall PHP Monolith Application"

# ---------------------------------------------------------------
# 1. Enable Apache mod_rewrite for clean URLs & .htaccess support
# ---------------------------------------------------------------
RUN a2enmod rewrite

# ---------------------------------------------------------------
# 2. Install required PHP extensions
# ---------------------------------------------------------------
RUN docker-php-ext-install \
    mysqli \
    pdo_mysql \
    mbstring \
    curl \
    gd

# ---------------------------------------------------------------
# 3. Copy PHP configuration for development
# ---------------------------------------------------------------
COPY docker/php.ini /usr/local/etc/php/conf.d/smartmall.ini

# ---------------------------------------------------------------
# 4. Copy application code
# ---------------------------------------------------------------
COPY . /var/www/html/

# ---------------------------------------------------------------
# 5. Allow .htaccess overrides for the web root
# ---------------------------------------------------------------
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# ---------------------------------------------------------------
# 6. Make entrypoint executable
# ---------------------------------------------------------------
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ---------------------------------------------------------------
# 7. Expose port 80
# ---------------------------------------------------------------
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
