#!/bin/sh
set -e

cd /app

if [ "$APP_ENV" = "dev" ] && [ -f composer.json ]; then
    if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
        echo "[entrypoint] Installing composer dependencies (dev)..."
        composer install --prefer-dist --no-interaction --no-progress
    fi

    if [ -f bin/console ]; then
        echo "[entrypoint] Installing importmap..."
        php bin/console importmap:install || true
    fi
fi

if [ "$RUN_MIGRATIONS" = "1" ] && [ -f bin/console ]; then
    echo "[entrypoint] Running doctrine migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

exec "$@"
