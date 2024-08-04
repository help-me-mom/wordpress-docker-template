FROM wordpress:6.5.5-php8.3-apache

ENTRYPOINT ["/usr/bin/docker-entrypoint.sh"]
CMD ["/usr/local/bin/docker-entrypoint.sh", "apache2-foreground"]

RUN apt-get update && \
    apt-get install -y msmtp && \
    apt-get clean && \
    a2enmod headers && \
    a2enmod ssl && \
    pecl install xdebug && \
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini

COPY /docker.dev /
RUN chmod 0755 /usr/bin/docker-entrypoint.sh && \
    chmod 0755 /docker-entrypoint.d/*.sh
