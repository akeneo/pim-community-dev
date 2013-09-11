#!/bin/bash
set -e

if [ "$1" != "" ]; then
  env=$1
else
  env="dev"
fi

php app/console fos:js-routing:dump --target=web/js/routes.js --no-debug
php app/console oro:navigation:init --env=$env --no-debug
php app/console assets:install web --env=$env --no-debug
php app/console assetic:dump --env=$env --no-debug
php app/console oro:assetic:dump --env=$env --no-debug
php app/console cache:clear --env=$env --no-debug
