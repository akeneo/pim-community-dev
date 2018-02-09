#!/usr/bin/env bash

docker-compose exec fpm php -d memory_limit=3G /usr/local/bin/composer update
docker-compose run --rm node yarn install
