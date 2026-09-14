FROM dunglas/frankenphp:php8.4 AS builder
WORKDIR "/application"

RUN install-php-extensions \
	pdo_mysql \
	zip \
	bcmath \
	sockets

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM builder AS vendor

COPY ./composer.json ./composer.lock ./

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/* \
 && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM builder AS application

COPY --from=vendor /application/vendor ./vendor
COPY . .

RUN mkdir -p \
  ./storage/framework/cache/data \
  ./storage/framework/sessions \
  ./storage/framework/views \
  ./storage/logs \
  ./storage/app/public \
  ./.scribe \
  ./bootstrap/cache
RUN composer dump-autoload --no-dev --optimize
RUN chown -R www-data:www-data ./bootstrap/cache ./storage ./config ./.scribe

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD php -r "exit(@file_get_contents('https://localhost/up') === false ? 1 : 0);"

ENTRYPOINT []

FROM application AS dev

RUN install-php-extensions \
	xdebug-3.5.1

FROM application AS queue

FROM application AS schedule
