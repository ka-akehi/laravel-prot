FROM laravel-base

WORKDIR /var/www
COPY . /var/www

COPY docker/horizon.conf /etc/supervisor/conf.d/horizon.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["entrypoint.sh"]
