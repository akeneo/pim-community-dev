#!/usr/bin/env bash

currentDir=$(dirname "$0")

echo "Clean previous install"

rm -rf ${currentDir}/../../app/archive/*
rm -rf ${currentDir}/../../var/cache/*
rm -rf ${currentDir}/../../app/file_storage/*
rm -rf ${currentDir}/../../var/logs/*
rm -rf ${currentDir}/../../web/bundles/*
rm -rf ${currentDir}/../../web/cache/*
rm -rf ${currentDir}/../../web/css/*
rm -rf ${currentDir}/../../web/dist/*
rm -rf ${currentDir}/../../web/js/*
rm -rf ${currentDir}/../../web/media/*

echo "Install the PIM database"

docker-compose exec fpm bin/console ca:c --env=prod
docker-compose exec fpm bin/console ca:c --env=behat

docker-compose exec fpm bin/console --env=prod pim:install --force
docker-compose exec fpm bin/console --env=behat pim:installer:db

echo "Install the assets"

docker-compose exec fpm bin/console --env=prod assets:install --symlink

docker-compose run node npm install
docker-compose run node npm run webpack
