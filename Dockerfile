FROM php:7.4-cli-alpine3.12 as builder
WORKDIR /application

ADD ./ .

RUN wget -O - https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --version=2.0.7 && \
    composer install

FROM php:7.4-cli-alpine3.12 as tester
WORKDIR /application

COPY --from=builder /application .

RUN apk add --no-cache --virtual build-dependencies \
    build-base \
    gcc \
    autoconf && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

RUN ./vendor/bin/phpunit

FROM php:7.4-cli-alpine3.12 as dev
WORKDIR /application

COPY --from=builder /application .
COPY --from=tester /apllication/public/code-coverage ./public/code-coverage
RUN apk add --no-cache &&\
    docker-php-ext-install sockets &&\
    vendor/bin/rr get-binary --location bin/ &&\
    bin/console doctrine:schema:up -f &&\
    bin/console doctrine:fixtures:load -n

CMD ["bin/rr", "serve"]