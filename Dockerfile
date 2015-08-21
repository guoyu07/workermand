FROM php:cli

MAINTAINER "443993225@qq.com"

RUN sed -i -e 's/httpredir.debian.org/mirrors.163.com/' /etc/apt/sources.list \
    && apt-get update \
    && apt-get -y install libevent-dev \
    && curl -SL http://pecl.php.net/get/libevent-0.1.0.tgz > /tmp/libevent-0.1.0.tgz \
    && tar zxvf /tmp/libevent-0.1.0.tgz -C /tmp \
    && mv /tmp/libevent-0.1.0 /usr/src/php/ext/libevent \
    && docker-php-ext-install pcntl sockets libevent \
    && rm -rf /tmp/libevent* \
    && rm -rf /usr/src/php/ext/libevent \
    && rm -rf /var/lib/apt/lists/*

ADD bin /usr/src/workermand/bin
ADD lib /usr/src/workermand/lib
ADD etc /usr/src/workermand/etc
ADD composer.json /usr/src/workermand/composer.json

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get update \
    && apt-get -y install git \
    && cd /usr/src/workermand \
    && composer install \
    && rm -rf /usr/local/bin/composer \
    && apt-get purge -y --auto-remove git \
    && rm -rf /var/lib/apt/lists/*

ADD doc/thrift-service/handler /usr/src/thrift-service/handler
ADD doc/thrift-service/gen-php /usr/src/thrift-service/gen-php
ADD doc/thrift-service/workermand.json /usr/src/workermand/etc/workermand.json

EXPOSE 9090
CMD ["/usr/src/workermand/bin/workermand"]
