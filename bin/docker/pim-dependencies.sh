#!/usr/bin/env bash

docker-compose exec fpm composer update
docker-compose run --rm node yarn install
