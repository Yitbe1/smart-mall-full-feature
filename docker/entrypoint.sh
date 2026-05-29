#!/bin/bash
# =============================================================================
# Smart Mall — Docker Entrypoint
# Runs optional composer install, executes database migrations,
# then starts Apache in the foreground.
# =============================================================================

set -e

# ---------------------------------------------------------------
# 1. Run composer install if composer.json exists
# ---------------------------------------------------------------
if [ -f /var/www/html/composer.json ]; then
    echo "--> Running composer install..."
    composer install --no-interaction --prefer-dist --working-dir=/var/www/html
else
    echo "--> No composer.json found, skipping composer install."
fi

# ---------------------------------------------------------------
# 2. Wait for MySQL to be ready, then run migrations
# ---------------------------------------------------------------
if [ -f /var/www/html/deploy/migrate.php ]; then
    echo "--> Waiting for MySQL at ${DB_HOST}:3306 ..."
    # Retry loop — MySQL may still be starting up
    for i in $(seq 1 30); do
        if php -r "new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME};charset=utf8mb4', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; then
            echo "--> MySQL is ready."
            break
        fi
        echo "--> Waiting... ($i/30)"
        sleep 2
    done

    echo "--> Running database migrations..."
    php /var/www/html/deploy/migrate.php
else
    echo "--> No deploy/migrate.php found, skipping migrations."
fi

# ---------------------------------------------------------------
# 3. Start Apache in foreground (PID 1)
# ---------------------------------------------------------------
echo "--> Starting Apache..."
apache2-foreground
