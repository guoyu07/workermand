FROM php:cli

MAINTAINER "443993225@qq.com"

RUN sed -i -e 's/httpredir.debian.org/mirrors.163.com/' /etc/apt/sources.list \
    && apt-get update \
    && apt-get -y install libevent-dev unzip git thrift-compiler \
    && curl -SL http://pecl.php.net/get/libevent-0.1.0.tgz > /tmp/libevent-0.1.0.tgz \
    && tar zxvf /tmp/libevent-0.1.0.tgz -C /tmp \
    && mv /tmp/libevent-0.1.0 /usr/src/php/ext/libevent \
    && docker-php-ext-install pcntl sockets libevent \
    && rm -rf /tmp/libevent* \
    && rm -rf /usr/src/php/ext/libevent

RUN curl -SL https://github.com/potterhe/workermand/archive/master.zip > /tmp/workermand.zip \
    && unzip /tmp/workermand.zip -d /tmp \
    && mv /tmp/workermand-master /usr/src/workermand \
    && rm -rf /tmp/workermand.zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && cd /usr/src/workermand \
    && composer install \
    && thrift -r -o doc/thrift-service --gen php:server doc/thrift-service/tutorial.thrift \
    && apt-get -y purge git unzip thrift-compiler \
    && apt-get -y autoremove \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /usr/local/bin/composer

EXPOSE 9090
CMD ["/usr/src/workermand/bin/workermand"]
