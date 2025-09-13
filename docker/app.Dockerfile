FROM laravel-base

WORKDIR /var/www

# プロジェクトをコピー
COPY . /var/www

# 必要なディレクトリを先に作成して権限付与
RUN mkdir -p bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
 && chmod -R a+w storage bootstrap/cache

# composer install & Laravel 初期セットアップ
RUN composer install --prefer-dist --optimize-autoloader --no-interaction
RUN php artisan key:generate

# root のまま entrypoint を起動
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
