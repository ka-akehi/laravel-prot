FROM php:8.3-cli

# 必要な拡張をインストール
RUN apt-get update && apt-get install -y \
    unzip git curl supervisor sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY . .

# Supervisor 設定フォルダを作成
RUN mkdir -p /etc/supervisor/conf.d

# supervisord.conf をコピー（後で作成）
COPY supervisord.conf /etc/supervisor/supervisord.conf

# laravel-worker.conf をコピー（後で作成）
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
