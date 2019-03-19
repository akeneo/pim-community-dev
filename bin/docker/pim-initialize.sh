#!/usr/bin/env bash
set -e

docker-compose run --rm php rm -rf var/cache/*

docker-compose run --rm php bin/console --env=prod pim:install --force --symlink --clean
docker-compose run --rm php bin/console --env=behat pim:installer:db

docker-compose run --rm node yarn run webpack-dev
docker-compose run --rm node yarn run webpack-test
