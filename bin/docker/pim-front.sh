#!/usr/bin/env bash

docker-composer exec akeneo --env=prod cache:clear --no-warmup
docker-composer exec akeneo --env=dev cache:clear --no-warmup
docker-composer exec akeneo-behat --env=behat cache:clear --no-warmup
docker-composer exec akeneo-behat --env=test cache:clear --no-warmup

docker-compose exec fpm bin/console --env=prod pim:installer:assets --clean --symlink

docker-compose run node npm install
docker-compose run node npm run webpack
