FROM php:5.6-fpm-alpine

ARG APP_ENV
ENV APP_ENV ${APP_ENV}

RUN mv /usr/local/etc/php/php.ini-${APP_ENV} /usr/local/etc/php/php.ini
COPY php.ini /usr/local/etc/php/conf.d/
COPY www.conf /usr/local/etc/php-fpm.d/

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
  && pecl install apcu-4.0.11 \
  && docker-php-ext-enable apcu \
  && docker-php-ext-install mysqli \
  && pecl clear-cache \
  && apk del .build-dependencies

#RUN apk add --update --no-cache autoconf build-base \
#  && pecl install xdebug-2.5.5 \
#  && docker-php-ext-enable xdebug
#COPY xdebug.ini /usr/local/etc/php/conf.d/ 

RUN apk add --update --no-cache --virtual tzdata \
  && cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime \
  && apk del tzdata \
  && rm -rf /var/cache/apk/*

