FROM laravel-base

COPY docker/horizon.conf /etc/supervisor/conf.d/horizon.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

CMD ["entrypoint.sh"]
