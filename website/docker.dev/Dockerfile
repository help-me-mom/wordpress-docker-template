FROM wordpress:6.4.3-php8.3-apache

RUN apt-get update && \
    apt-get install -y msmtp && \
    apt-get clean && \
    a2enmod headers && \
    pecl install xdebug && \
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini

COPY /docker.dev /
