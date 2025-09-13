#!/bin/sh

set -e

echo "▶️ Composer install (safe re-run)"
composer install --prefer-dist --optimize-autoloader --no-interaction

echo "📁 Ensuring storage & cache dirs"
mkdir -p bootstrap/cache \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs

echo "🔧 Laravel setup"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan package:discover

if [ "$ENTRYPOINT_MODE" = "supervisor" ]; then
  echo "🚀 Starting supervisord"
  exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
else
  echo "🚀 Starting Laravel dev server"
  exec php artisan serve --host=0.0.0.0 --port=8000
fi
