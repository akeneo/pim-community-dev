#!/bin/bash

if [ "$1" != "" ]; then
  env=$1
else
  env="dev"
fi

php app/console doctrine:database:drop --force --env=$env
php app/console doctrine:database:create  --env=$env
php app/console doctrine:schema:update --force --env=$env
php app/console doctrine:fixtures:load --no-interaction --env=$env
php app/console oro:acl:load --env=$env
php app/console oro:search:create-index --env=$env
php app/console oro:search:index --env=$env
php app/console oro:search:reindex --env=$env

