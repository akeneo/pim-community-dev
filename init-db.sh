#!/bin/bash
set -e

if [ "$1" != "" ]; then
  env=$1
else
  env="dev"
fi

php app/console doctrine:database:drop --force --env=$env
php app/console doctrine:database:create  --env=$env
php app/console doctrine:schema:update --force --env=$env
if [ $env != "behat" ]; then
    php app/console oro:search:create-index --env=$env
    php app/console doctrine:fixtures:load --no-interaction --env=$env --no-debug
    php app/console oro:acl:load --env=$env
    php app/console pim:search:reindex en_US --env=$env
    php app/console pim:versioning:refresh
else
    php app/console cache:clear --env=$env
fi
