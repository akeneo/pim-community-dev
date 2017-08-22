#!/usr/bin/env bash

docker-compose exec akeneo app/console --env=prod cache:clear --no-warmup
docker-compose exec akeneo app/console --env=dev cache:clear --no-warmup
docker-compose exec akeneo-behat app/console --env=behat cache:clear --no-warmup
docker-compose exec akeneo-behat app/console --env=test cache:clear --no-warmup

docker-compose exec akeneo app/console --env=prod pim:installer:assets --symlink --clean

docker-compose run --rm node npm install
docker-compose run --rm node npm run webpack
