#!/usr/bin/env bash
set -e

docker-compose run --rm php composer install
docker-compose run --rm node yarn install
