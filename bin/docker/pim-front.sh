#!/usr/bin/env bash

docker-compose exec fpm bin/console --env=prod cache:clear --no-warmup
docker-compose exec fpm bin/console --env=dev cache:clear --no-warmup
docker-compose exec fpm bin/console --env=behat cache:clear --no-warmup
docker-compose exec fpm bin/console --env=test cache:clear --no-warmup

docker-compose exec fpm bin/console --env=prod pim:installer:assets --symlink --clean

docker-compose run --rm node yarn run webpack-dev
