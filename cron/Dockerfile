FROM php:5.6-alpine

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY php.ini /usr/local/etc/php/conf.d/

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
  && docker-php-ext-install mysqli \
  && apk del .build-dependencies

RUN apk add --update --no-cache --virtual tzdata \
  && cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime \
  && apk del tzdata \
  && rm -rf /var/cache/apk/*

COPY cron.hateblog /var/spool/cron/crontabs/root

CMD ["crond", "-f", "-l", "2"]
