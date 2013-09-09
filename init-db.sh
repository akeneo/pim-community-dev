#!/bin/bash
set -e

if [ "$1" != "" ]; then
  env=$1
else
  env="dev"
fi

php app/console doctrine:database:drop --force --env $env
php app/console doctrine:database:create  --env $env
php app/console doctrine:schema:create --env $env
if [ $env != "behat" ]; then
    php app/console doctrine:fixture:load --no-debug --no-interaction --env $env
fi
php app/console oro:acl:load --env $env
php app/console oro:entity-config:update --env $env
php app/console oro:entity-extend:create --env $env
php app/console cache:clear --env $env
php app/console doctrine:schema:update --env $env --force
php app/console oro:search:create-index --env $env
php app/console pim:search:reindex en_US --env $env
php app/console pim:versioning:refresh --env $env
php app/console pim:product:completeness-calculator --env $env

