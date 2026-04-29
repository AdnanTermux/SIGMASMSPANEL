# ── Sigma SMS A2P — Railway Dockerfile ───────────────────────────────────────
# PHP 8.2 + Apache with pdo_mysql, mod_rewrite, and all required extensions.

FROM php:8.2-apache

# ── System dependencies ───────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions ────────────────────────────────────────────────────────────
RUN docker-php-ext-install pdo pdo_mysql mysqli

# ── Apache modules ────────────────────────────────────────────────────────────
RUN a2enmod rewrite headers deflate

# ── PHP runtime config ────────────────────────────────────────────────────────
RUN { \
    echo "display_errors = Off"; \
    echo "log_errors = On"; \
    echo "upload_max_filesize = 32M"; \
    echo "post_max_size = 32M"; \
    echo "max_execution_time = 60"; \
    echo "session.cookie_httponly = 1"; \
    echo "session.use_strict_mode = 1"; \
} >> /usr/local/etc/php/php.ini

# ── Apache virtual host ───────────────────────────────────────────────────────
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# ── Application files ─────────────────────────────────────────────────────────
COPY sigma_sms/ /var/www/html/sigma_sms/

# ── Permissions ───────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html/sigma_sms \
 && find /var/www/html/sigma_sms -type d -exec chmod 755 {} \; \
 && find /var/www/html/sigma_sms -type f -exec chmod 644 {} \;

# ── Entrypoint: adjust Apache port to Railway's $PORT ─────────────────────────
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

CMD ["/docker-entrypoint.sh"]
