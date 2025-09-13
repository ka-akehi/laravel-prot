FROM laravel-base

COPY docker/redis-worker.conf /etc/supervisor/conf.d/redis-worker.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

CMD ["entrypoint.sh"]
