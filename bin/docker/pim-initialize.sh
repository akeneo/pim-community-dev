#!/usr/bin/env bash
set -e

docker-compose exec fpm rm -rf var/cache/*

docker-compose exec fpm bin/console --env=prod pim:install --force --symlink --clean
docker-compose exec fpm bin/console --env=behat pim:installer:db

docker-compose run --rm node yarn run webpack-dev
docker-compose run --rm node yarn run webpack-test
