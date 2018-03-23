#!/usr/bin/env bash

docker-compose exec fpm rm -rf var/cache/*

docker-compose exec fpm bin/console --env=prod pim:installer:assets --symlink --clean

docker-compose run --rm node yarn run webpack-dev
