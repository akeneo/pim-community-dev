#!/usr/bin/env bash

docker-compose exec akeneo rm -rf app/cache/*

docker-compose exec akeneo app/console --env=prod pim:install --force --symlink --clean
docker-compose exec akeneo-behat app/console --env=behat pim:installer:db
