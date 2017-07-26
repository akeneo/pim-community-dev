#!/usr/bin/env bash

currentDir=$(dirname "$0")

echo "Clean previous assets"

rm -rf ${currentDir}/../../app/cache/*

echo "Install the assets"

docker-compose exec akeneo app/console --env=prod pim:installer:assets --symlink
