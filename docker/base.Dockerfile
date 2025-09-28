FROM php:8.3-fpm AS laravel-base

ARG GH_OST_VERSION=1.1.5

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip supervisor wget gnupg lsb-release \
    percona-toolkit \
    default-mysql-client \
    \
    # gh-ost リリース URL 抽出（アセット名 “gh-ost-binary-linux-amd64” 含む） \
    && GH_OST_URL=$(curl -s https://api.github.com/repos/github/gh-ost/releases/latest \
         | grep "browser_download_url.*gh-ost-binary-linux-amd64" \
         | sed -E 's/.*"(https.*gh-ost-binary-linux-amd64[^"]+)".*/\1/') \
    && if [ -z "$GH_OST_URL" ]; then echo "Unable to find gh-ost binary URL" >&2; exit 1; fi \
    && echo "Downloading gh-ost from $GH_OST_URL" \
    && curl -sSfL "$GH_OST_URL" -o /tmp/gh-ost.tgz \
    && tar -xzf /tmp/gh-ost.tgz -C /tmp \
    && mv /tmp/gh-ost /usr/local/bin/gh-ost \
    && chmod +x /usr/local/bin/gh-ost \
    \
    # PHP 拡張と Redis etc. \
    && docker-php-ext-install pdo pdo_mysql zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    \
    && rm -rf /tmp/* \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
