#!/usr/bin/env bash

currentDir=$(dirname "$0")

echo "Clean previous assets"

rm -rf ${currentDir}/../../var/cache/*
rm -rf ${currentDir}/../../web/bundles/*
rm -rf ${currentDir}/../../web/cache/*
rm -rf ${currentDir}/../../web/css/*
rm -rf ${currentDir}/../../web/dist/*
rm -rf ${currentDir}/../../web/js/*

echo "Install the assets"

docker-compose exec fpm bin/console ca:c --env=prod

docker-compose exec fpm bin/console --env=prod pim:installer:assets
docker-compose exec fpm bin/console --env=prod assets:install --symlink

docker-compose run node npm install
docker-compose run node npm run webpack
