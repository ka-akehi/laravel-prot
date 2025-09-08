FROM laravel-base

WORKDIR /var/www
COPY . .
COPY docker/redis-worker.conf /etc/supervisor/conf.d/redis-worker.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["entrypoint.sh"]
